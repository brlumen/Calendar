<?php
# Copyright (c) 2025 Grigoriy Ermolaev (igflocal@gmail.com)
# Calendar for MantisBT is free software: 
# you can redistribute it and/or modify it under the terms of the GNU
# General Public License as published by the Free Software Foundation, 
# either version 2 of the License, or (at your option) any later version.
#
# Calendar plugin for for MantisBT is distributed in the hope 
# that it will be useful, but WITHOUT ANY WARRANTY; without even the 
# implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
# See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Customer management plugin for MantisBT.  
# If not, see <http://www.gnu.org/licenses/>.

auth_ensure_user_authenticated();

access_ensure_project_level( plugin_config_get( 'calendar_view_threshold' ) );

# Get view type (week/month)
$f_view_type = gpc_get_string( 'view', '' );

// Если вид не указан в параметрах, пробуем получить из куки
if (empty($f_view_type)) {
    $f_view_type = gpc_get_cookie('calendar_view_type', 'week');
} else {
    // Сохраняем выбранный вид в куки на 1 год
    gpc_set_cookie('calendar_view_type', $f_view_type, true);
}

layout_page_header( plugin_lang_get( $f_view_type ) );

layout_page_begin( plugin_page( 'calendar_user_page' ) );

# Get Project Id and set it as current
$t_project_id = gpc_get_int( 'project_id', helper_get_current_project() );
if( ( ALL_PROJECTS == $t_project_id || project_exists( $t_project_id ) ) && $t_project_id != helper_get_current_project()
 ) {
    helper_set_current_project( $t_project_id );
    # Reloading the page is required so that the project browser
    # reflects the new current project
    print_header_redirect( $_SERVER['REQUEST_URI'], true, false, true );
}

compress_enable();

# don't index view issues pages
html_robots_noindex();

$t_current_week = date( "W" );
$t_current_month = date( "n" );
$t_current_year = date( "o" );

$f_string_date_selected = gpc_get_string( "date_select", '' );
$p_date_selected = $f_view_type === 'week' ? strtotime( $f_string_date_selected ) : strtotime( '01-'.$f_string_date_selected );

$f_week        = gpc_get_int( "week", !$p_date_selected ? $t_current_week : date( "W", $p_date_selected ) );
$f_month       = gpc_get_int( "month", !$p_date_selected ? $t_current_month : date( "n", $p_date_selected ) );
$f_year        = gpc_get_int( "year", !$p_date_selected ? $t_current_year : date( "o", $p_date_selected ) );
$f_is_fulltime = gpc_get_bool( "full_time" );
$f_for_user    = gpc_get_int( "for_user", auth_get_current_user_id() );
//$t_access_level_current_user        = access_get_project_level();
//$t_access_level_global_current_user = access_get_global_level();

if( $f_view_type === 'week' ) {
    if( strtotime( $f_year . 'W' . str_pad( $f_week, 2, 0, STR_PAD_LEFT ) ) == false ) {
        error_parameters( plugin_lang_get( 'date_event' ) );
        plugin_error( 'ERROR_RANGE_TIME', ERROR );
    }

    $t_start_day_of_the_week = plugin_config_get( "startStepDays" );
    $t_step_days_count       = plugin_config_get( "countStepDays" );
    $t_arWeekdaysName        = plugin_config_get( "arWeekdaysName" );

    $t_days        = days_of_number_week( $t_start_day_of_the_week, $t_step_days_count, $t_arWeekdaysName, $f_week, $f_year );
    $t_days_events = get_days_object( $t_days, helper_get_current_project(), $f_for_user );

    $t_calendar = new ViewWeekCalendar( $f_week, $f_for_user, $f_is_fulltime, $t_days_events, plugin_page( 'view' ), $f_year, $p_date_selected );
} else {
    # Получаем первый день месяца и определяем количество дней
    $t_first_date = strtotime(sprintf('%04d-%02d-01', $f_year, $f_month));
    $t_first_weekday = date('w', $t_first_date);
    $t_first_day = ($t_first_weekday == 0) ? 6 : $t_first_weekday - 1;
    
    # Получаем дни предыдущего месяца
    $t_prev_month = $f_month == 1 ? 12 : $f_month - 1;
    $t_prev_year = $f_month == 1 ? $f_year - 1 : $f_year;
    $t_days_in_prev_month = date('t', strtotime("$t_prev_year-$t_prev_month-01"));
    $t_start_day_prev_month = $t_days_in_prev_month - $t_first_day + 1;
    
    # Получаем дни текущего месяца
    $t_days_in_month = date('t', $t_first_date);
    
    # Вычисляем количество дней следующего месяца
    $t_cells = $t_first_day + $t_days_in_month;
    $t_days_in_next_month = ceil($t_cells / 7) * 7 - $t_cells;
    
    # Создаем массив всех дней для отображения
    $t_days = array();
    
    # Добавляем дни предыдущего месяца
    for ($i = $t_start_day_prev_month; $i <= $t_days_in_prev_month; $i++) {
        $t_timestamp = strtotime("$t_prev_year-$t_prev_month-$i");
        $t_days[] = $t_timestamp;
    }
    
    # Добавляем дни текущего месяца
    for ($i = 1; $i <= $t_days_in_month; $i++) {
        $t_timestamp = strtotime("$f_year-$f_month-$i");
        $t_days[] = $t_timestamp;
    }
    
    # Добавляем дни следующего месяца
    $t_next_month = $f_month == 12 ? 1 : $f_month + 1;
    $t_next_year = $f_month == 12 ? $f_year + 1 : $f_year;
    for ($i = 1; $i <= $t_days_in_next_month; $i++) {
        $t_timestamp = strtotime("$t_next_year-$t_next_month-$i");
        $t_days[] = $t_timestamp;
    }
    
    # Получаем события для всех дней
    $t_days_events = get_days_object($t_days, helper_get_current_project(), $f_for_user);
    
    # Преобразуем формат дат в массиве событий
    $t_formatted_days_events = array();
    foreach($t_days_events as $t_timestamp => $t_events) {
        $t_date = date('Y-m-d', $t_timestamp);
        $t_formatted_days_events[$t_date] = $t_events;
    }
    
    $t_calendar = new ViewMonthCalendar($f_month, $f_for_user, $t_formatted_days_events, plugin_page('view'), $f_year, $p_date_selected);
}

$t_calendar->print_html();

layout_page_end();
