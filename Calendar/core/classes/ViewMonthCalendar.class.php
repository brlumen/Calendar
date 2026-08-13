<?php
# Copyright (c) 2025 Grigoriy Ermolaev (igflocal@gmail.com)
# Calendar for MantisBT is free software

class ViewMonthCalendar {
    private $month;
    private $year;
    private $user_id;
    private $days_events;
    private $link_to_view;
    private $date_selected;

    public function __construct($p_month, $p_user_id, $p_days_events, $p_link_to_view, $p_year, $p_date_selected = false) {
        $this->month = $p_month;
        $this->year = $p_year;
        $this->user_id = $p_user_id;
        $this->days_events = $p_days_events;
        $this->link_to_view = $p_link_to_view;
        $this->date_selected = $p_date_selected;
    }

    public function print_html() {
        $t_days_in_month = date('t', strtotime("$this->year-$this->month-01"));
        // Получаем день недели (0 = воскресенье, 1 = понедельник, ..., 6 = суббота)
        $t_first_weekday = date('w', strtotime("$this->year-$this->month-01"));
        // Преобразуем в формат, где понедельник = 0, вторник = 1, ..., воскресенье = 6
        $t_first_day = ($t_first_weekday == 0) ? 6 : $t_first_weekday - 1;

        echo '<div class="col-md-12 col-xs-12">';
        echo '<div class="space-10"></div>';
        echo '<div class="widget-box widget-color-blue2">';
        echo '<div class="widget-header widget-header-small">';
        echo '<h4 class="widget-title lighter">';
        echo '<i class="ace-icon fa fa-calendar"></i>';
        echo plugin_lang_get(strtolower(date('F', strtotime("$this->year-$this->month-01")))) . ' ' . $this->year;
        echo '</h4>';
        
        $this->print_navigation();
        
        echo '</div>';
        echo '<div class="widget-body">';
        
        $this->print_menu_top();
        
        echo '<div class="widget-main no-padding">';
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered table-condensed table-striped">';
        
        $this->print_header();
        $this->print_calendar_body($t_days_in_month, $t_first_day);
        
        echo '</table>';
        echo '</div>';
        
        $this->print_menu_bottom();
        
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Добавляем модальное окно для просмотра событий
        echo '<div id="eventModal" class="modal" style="display: none;"
              data-events-for-date-text="' . plugin_lang_get("events_for_date") . '"
              data-add-event-text="' . plugin_lang_get("add_new_event") . '">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="close">&times;</span>
                    <h2 id="modalTitle"></h2>
                </div>
                <div class="modal-body">
                    <div id="modalEventList"></div>
                    <div class="modal-create-event">
                        <button id="create-event-btn" type="button" class="btn btn-primary btn-white btn-round">
                            <i class="ace-icon fa fa-plus"></i> ' . plugin_lang_get("add_new_event") . '
                        </button>
                    </div>
                    <div id="createEventForm" style="display: none;">
                        <hr>
                        <div class="form-group">
                            <label>' . plugin_lang_get("date_event") . ': <span id="selectedDate" class="form-control-static"></span></label>
                        </div>
                        <div class="form-group">
                            <label for="eventName">' . plugin_lang_get("name_event") . '</label>
                            <input type="text" id="eventName" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="eventTimeStart">' . plugin_lang_get("from_time") . '</label>
                            <select id="eventTimeStart" class="form-control">';
        print_time_select_option( null, true );
        echo '</select>
                        </div>
                        <div class="form-group">
                            <label for="eventTimeEnd">' . plugin_lang_get("to_time") . '</label>
                            <select id="eventTimeEnd" class="form-control">';
        print_time_select_option( null, true );
        echo '</select>
                        </div>
                        <button type="button" id="createEventBtn" class="btn btn-primary btn-white btn-round">' . 
                            plugin_lang_get("add_button") . 
                        '</button>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function print_navigation() {
        echo '<div class="widget-toolbar">';
        echo '</div>';
    }

    private function print_header() {
        echo '<thead>';
        echo '<tr>';
        echo '<th style="border: none; background: none;"></th>';
        echo '<th style="text-align: center;">' . plugin_lang_get('Mon') . '</th>';
        echo '<th style="text-align: center;">' . plugin_lang_get('Tue') . '</th>';
        echo '<th style="text-align: center;">' . plugin_lang_get('Wed') . '</th>';
        echo '<th style="text-align: center;">' . plugin_lang_get('Thu') . '</th>';
        echo '<th style="text-align: center;">' . plugin_lang_get('Fri') . '</th>';
        echo '<th style="text-align: center;">' . plugin_lang_get('Sat') . '</th>';
        echo '<th style="text-align: center;">' . plugin_lang_get('Sun') . '</th>';
        echo '</tr>';
        echo '</thead>';
    }

    private function get_event_url($p_event, $p_date) {
        return $this->link_to_view . '&event_id=' . $p_event['id'] . 
               ( isset( $p_event['recurrence_pattern'] ) && !is_blank( $p_event['recurrence_pattern'] ) ? '&date=' . $p_event['date_from'] : '');
    }

    private function print_calendar_body($p_days_in_month, $p_first_day) {
        echo '<tbody>';
        
        $t_day_count = 1;
        $t_cells = 0;
        $t_slots_per_day = 3;
        
        // Получаем даты для предыдущего месяца
        $t_prev_month = $this->month == 1 ? 12 : $this->month - 1;
        $t_prev_year = $this->month == 1 ? $this->year - 1 : $this->year;
        $t_days_in_prev_month = date('t', strtotime("$t_prev_year-$t_prev_month-01"));
        $t_start_day_prev_month = $t_days_in_prev_month - $p_first_day + 1;
        
        while ($t_day_count <= $p_days_in_month) {
            if ($t_cells % 7 == 0) {
                echo '<tr>';
                // Добавляем ячейку с номером недели без ведущего нуля
                $t_week_number = date('W', strtotime(sprintf('%04d-%02d-%02d', 
                    $t_cells < $p_first_day ? $t_prev_year : $this->year,
                    $t_cells < $p_first_day ? $t_prev_month : $this->month,
                    $t_cells < $p_first_day ? $t_start_day_prev_month : $t_day_count
                )));
                echo '<td class="calendar-week-number">' . intval($t_week_number) . '</td>';
            }
            
            if ($t_cells < $p_first_day) {
                // Отображаем дни предыдущего месяца
                $t_date = sprintf('%04d-%02d-%02d', $t_prev_year, $t_prev_month, $t_start_day_prev_month);
                $t_is_today = strtotime(date('Y-m-d')) == strtotime($t_date);
                
                $t_day_events = array();
                if (isset($this->days_events[$t_date])) {
                    $t_day_events = $this->days_events[$t_date];
                    usort($t_day_events, function($a, $b) {
                        return $a['date_from'] - $b['date_from'];
                    });
                }
                
                $t_is_full = count($t_day_events) >= $t_slots_per_day;
                echo '<td class="calendar-cell other-month' . 
                     ($t_is_today ? ' calendar-today' : '') . 
                     '" data-date="' . $t_date . '">';
                echo '<div class="calendar-day-header' . ($t_is_full ? ' clickable' : '') . '" data-date="' . $t_date . '">';
                echo '<div class="calendar-day-number">' . $t_start_day_prev_month . '</div>';
                echo '</div>';
                
                // Выводим события для предыдущего месяца
                for ($i = 0; $i < $t_slots_per_day; $i++) {
                    if (isset($t_day_events[$i])) {
                        $t_event = $t_day_events[$i];
                        $t_event_time = date('H:i', $t_event['date_from']);
                        $t_event_duration = $t_event['duration'] / 3600;
                        
                        echo '<div class="calendar-event">';
                        echo '<a href="' . $this->get_event_url($t_event, $t_date) . '">';
                        echo '<span class="event-time">' . $t_event_time . '</span> ';
                        echo '<span class="event-duration">(' . number_format($t_event_duration, 1) . 'ч)</span> ';
                        echo string_html_specialchars($t_event['name']);
                        echo '</a>';
                        echo '</div>';
                    } else {
                        echo '<div class="calendar-event-empty clickable" data-date="' . $t_date . '"></div>';
                    }
                }
                
                $t_start_day_prev_month++;
                echo '</td>';
                $t_cells++;
                continue;
            }
            
            // Текущий месяц
            $t_date = sprintf('%04d-%02d-%02d', $this->year, $this->month, $t_day_count);
            $t_is_today = strtotime(date('Y-m-d')) == strtotime($t_date);
            
            $t_day_events = array();
            if (isset($this->days_events[$t_date])) {
                $t_day_events = $this->days_events[$t_date];
                usort($t_day_events, function($a, $b) {
                    return $a['date_from'] - $b['date_from'];
                });
            }
            
            $t_is_full = count($t_day_events) >= $t_slots_per_day;
            echo '<td class="calendar-cell' . 
                 ($t_is_today ? ' calendar-today' : '') . 
                 '" data-date="' . $t_date . '">';
            echo '<div class="calendar-day-header' . ($t_is_full ? ' clickable' : '') . '" data-date="' . $t_date . '">';
            echo '<div class="calendar-day-number">' . $t_day_count . '</div>';
            echo '</div>';
            
            // Выводим первые 4 события
            for ($i = 0; $i < $t_slots_per_day; $i++) {
                if (isset($t_day_events[$i])) {
                    $t_event = $t_day_events[$i];
                    $t_event_time = date('H:i', $t_event['date_from']);
                    $t_event_duration = $t_event['duration'] / 3600;
                    
                    echo '<div class="calendar-event">';
                    echo '<a href="' . $this->get_event_url($t_event, $t_date) . '">';
                    echo '<span class="event-time">' . $t_event_time . '</span> ';
                    echo '<span class="event-duration">(' . number_format($t_event_duration, 1) . 'ч)</span> ';
                    echo string_html_specialchars($t_event['name']);
                    echo '</a>';
                    echo '</div>';
                } else {
                    echo '<div class="calendar-event-empty clickable" data-date="' . $t_date . '"></div>';
                }
            }
            
            // Если есть дополнительные события, показываем информацию о них
            $t_remaining_events = count($t_day_events) - $t_slots_per_day;
            if ($t_remaining_events > 0) {
                // Подготавливаем данные для модального окна
                $t_modal_events = array();
                foreach ($t_day_events as $t_event) {
                    $t_modal_events[] = array(
                        'time' => date('H:i', $t_event['date_from']),
                        'duration' => number_format($t_event['duration'] / 3600, 1) . 'ч',
                        'name' => string_html_specialchars($t_event['name']),
                        'url' => $this->get_event_url($t_event, $t_date),
                        'project_name' => string_html_specialchars(project_get_name($t_event['project_id']))
                    );
                }
                
                echo '<div class="calendar-event calendar-more-events" data-date="' . 
                     date('d.m.Y', strtotime($t_date)) . '" data-events=\'' . 
                     json_encode($t_modal_events) . '\'>';
                echo '<i class="ace-icon fa fa-plus-circle">';
                echo sprintf(plugin_lang_get('more_events'), $t_remaining_events);
                echo '</i>';
                echo '</div>';
            } else {
                echo '<div class="calendar-event-empty clickable" data-date="' . $t_date . '"></div>';
            }
            
            echo '</td>';
            
            if ($t_cells % 7 == 6) {
                echo '</tr>';
            }
            
            $t_day_count++;
            $t_cells++;
        }
        
        // Отображаем дни следующего месяца
        $t_next_month = $this->month == 12 ? 1 : $this->month + 1;
        $t_next_year = $this->month == 12 ? $this->year + 1 : $this->year;
        $t_next_day = 1;
        
        while ($t_cells % 7 != 0) {
            $t_date = sprintf('%04d-%02d-%02d', $t_next_year, $t_next_month, $t_next_day);
            $t_is_today = strtotime(date('Y-m-d')) == strtotime($t_date);
            
            $t_day_events = array();
            if (isset($this->days_events[$t_date])) {
                $t_day_events = $this->days_events[$t_date];
                usort($t_day_events, function($a, $b) {
                    return $a['date_from'] - $b['date_from'];
                });
            }
            
            $t_is_full = count($t_day_events) >= $t_slots_per_day;
            echo '<td class="calendar-cell other-month' . 
                 ($t_is_today ? ' calendar-today' : '') . 
                 '" data-date="' . $t_date . '">';
            echo '<div class="calendar-day-header' . ($t_is_full ? ' clickable' : '') . '" data-date="' . $t_date . '">';
            echo '<div class="calendar-day-number">' . $t_next_day . '</div>';
            echo '</div>';
            
            // Выводим события для следующего месяца
            for ($i = 0; $i < $t_slots_per_day; $i++) {
                if (isset($t_day_events[$i])) {
                    $t_event = $t_day_events[$i];
                    $t_event_time = date('H:i', $t_event['date_from']);
                    $t_event_duration = $t_event['duration'] / 3600;
                    
                    echo '<div class="calendar-event">';
                    echo '<a href="' . $this->get_event_url($t_event, $t_date) . '">';
                    echo '<span class="event-time">' . $t_event_time . '</span> ';
                    echo '<span class="event-duration">(' . number_format($t_event_duration, 1) . 'ч)</span> ';
                    echo string_html_specialchars($t_event['name']);
                    echo '</a>';
                    echo '</div>';
                } else {
                    echo '<div class="calendar-event-empty clickable" data-date="' . $t_date . '"></div>';
                }
            }
            
            echo '</td>';
            $t_next_day++;
            $t_cells++;
        }
        
        echo '</tbody>';
    }

    private function print_empty_slots($p_count) {
        echo '<div class="calendar-day-number">&nbsp;</div>';
        for ($i = 0; $i < $p_count + 1; $i++) { // +1 для слота с дополнительной информацией
            echo '<div class="calendar-event-empty"></div>';
        }
    }

    protected function print_menu_top() {
        echo '<div class="widget-toolbox padding-8 clearfix">';      
        echo '<div class="btn-toolbar">';
        
        # Кнопка переключения на недельный вид
        echo '<div class="btn-group">';
        $t_url = plugin_page('calendar_user_page') . 
                '&view=week' . 
                '&week=' . date('W') . 
                '&year=' . date('Y');
        if (!is_bool($this->date_selected)) {
            $t_url .= '&date_select=' . date(plugin_config_get('short_date_format'), $this->date_selected);
        }
        if ($this->user_id != auth_get_current_user_id()) {
            $t_url .= '&for_user=' . $this->user_id;
        }
        print_small_button($t_url, plugin_lang_get('week_view'));
        echo '</div>';
        

        # Блок навигации
        echo '<div id="nav-button" class="btn-group pull-right">';

        # Блок выбора даты
        echo '<form id="select_date_form" method="post" action="' . plugin_page('calendar_user_page') . '" class="form-inline pull-left padding-left-8">';
        echo '<input type="hidden" name="view" value="month">';
        
//        if (!is_bool($this->date_selected)) {
//            $t_date_to_display = date(plugin_config_get('month_date_format'), $this->date_selected);
//        } else {
//            $t_date_to_display = plugin_lang_get(strtolower(date('F', strtotime("$this->year-$this->month-01")))) . ' ' . $this->year;
        $t_date_to_display = date(plugin_config_get('month_date_format'), strtotime("$this->year-$this->month-01"));
//        }
        
        echo '<input type="text" id="view_month_date_select" name="date_select" class="datetimepicker input-sm" ' .
             'data-picker-locale="' . lang_get_current_datetime_locale() . '" ' .
             'data-picker-format="' . plugin_config_get('datetime_picker_month_date_format') . '" ' .
             'size="10" maxlength="10" autocomplete="off" ' .
             'value="' . $t_date_to_display . '" ' .
             '/>';

        echo '</form>';
        
        # Кнопки навигации
        print_small_button( plugin_page('calendar_user_page') . '&view=month&month=' . ($this->month == 1 ? 12 : $this->month - 1) . '&year=' . ($this->month == 1 ? $this->year - 1 : $this->year), '<<' );
        print_small_button( plugin_page('calendar_user_page') . '&view=month&month=' . date('m') . '&year=' . date('Y'), plugin_lang_get('current_period') );
        print_small_button( plugin_page('calendar_user_page') . '&view=month&month=' . ($this->month == 12 ? 1 : $this->month + 1) . '&year=' . ($this->month == 12 ? $this->year + 1 : $this->year), '>>' );
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }

    protected function print_menu_bottom() {
        echo '<div class="widget-toolbox padding-8 clearfix">';
        echo '<div class="btn-toolbar">';

        echo '<div class="btn-group pull-left">';
        if( access_compare_level( access_get_project_level(), plugin_config_get( 'report_event_threshold' ) ) ) {
            print_small_button( plugin_page( 'event_add_page' ), plugin_lang_get( 'add_new_event' ) );
        }
        echo '</div>';

        echo '<div id="nav-button" class="btn-group pull-right">';

        echo '<form id="filter-queries-form" class="btn-toolbar"  method="get" name="list_queries" action="' . plugin_page( 'calendar_user_page' ) . '">';
        echo '<input type="hidden" name="page" value="Calendar/calendar_user_page" />';
        echo '<input type="hidden" name="view" value="month" />';
        echo '<input type="hidden" name="month" value="' . $this->month . '" />';
        echo '<input type="hidden" name="year" value="' . $this->year . '" />';

        echo '<label class="inline"></label>';
        echo '<select name="for_user">';
        echo '<option value="' . auth_get_current_user_id() . '">[' . lang_get( 'reset_query' ) . ']</option>';
        if( $this->user_id == 0 ) {
            echo '<option selected="selected" value="0">[' . plugin_lang_get( 'select_all_users' ) . ']</option>';
        } else {
            echo '<option value="0">[' . plugin_lang_get( 'select_all_users' ) . ']</option>';
        }

        print_user_option_list( $this->user_id, helper_get_current_project(), plugin_config_get( 'report_event_threshold' ) );

        echo '</select>';
        echo '</form>';

        echo '</div>';

        echo '</div>';
//        echo '</div>';
    }
} 