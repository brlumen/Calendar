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

/**
 * Description of WeekView
 *
 * @author ermolaev
 */
class ViewWeekCalendar extends WeekCalendar {
    protected $week;
    protected $for_user;
    protected $year;
    protected $date_selected;

    //put your code here
    public function __construct( $p_week, $p_user, $p_is_full_time, $p_days_events, $p_link_options, $p_year, $p_date_selected = false ) {
        parent::__construct($p_days_events, $p_link_options, $p_is_full_time);

        $this->week         = $p_week;
        $this->for_user     = (int)$p_user;
        $this->year         = $p_year;
        
        $this->date_selected = $p_date_selected;
    }

    protected function print_spacer_top() {
        echo '<div class="space-10">';
        echo '</div>';
    }

    protected function print_headline() {

        echo '<div class="widget-header widget-header-small">';

        echo '<h4 class="widget-title lighter">';
        echo '<i class="ace-icon fa fa-list-alt"></i>';
        echo plugin_lang_get( 'menu_main_front' ) . " ( GMT " . date( "P" ) . " )";
        echo '</h4>';

        if( access_compare_level( access_get_global_level(), plugin_config_get( 'manage_calendar_threshold' ) ) ) {
            echo '<div class="widget-toolbar no-border">';
            echo '<div class="widget-menu">';
            print_small_button( plugin_page( 'user_config_page' ), plugin_lang_get( 'config_title' ) );
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
    }

    protected function print_menu_top() {
        echo '<div class="widget-toolbox padding-8 clearfix">';      
        echo '<div class="btn-toolbar">';
        echo '<form id="select_date_form" method="post" action="' . plugin_page( 'calendar_user_page' ) . '" class="btn-toolbar">';        
        echo '<div class="btn-group pull-left">';
        
        if( !is_bool( $this->date_selected ) ) {
            $t_date_to_display = date( plugin_config_get( 'short_date_format' ), $this->date_selected );
	} else {
            $t_date_to_display = '';
        }
        
        if( self::$full_time_is == FALSE ) {
            print_hidden_inputs( array( 'full_time' => 'FALSE' ) );
            print_small_button( plugin_page( 'calendar_user_page' ) . "&for_user=" . $this->for_user . "&week=" . $this->week . "&year=" . $this->year . "&full_time=TRUE" . "&date_select=" . $t_date_to_display, "0-24" );
        } else {
            print_hidden_inputs( array( 'full_time' => 'TRUE' ) );
            print_small_button( plugin_page( 'calendar_user_page' ) . "&for_user=" . $this->for_user . "&week=" . $this->week . "&year=" . $this->year . "&date_select=" . $t_date_to_display, gmdate( "H", plugin_config_get( 'time_day_start' ) ) . "-" . gmdate( "H", plugin_config_get( 'time_day_finish' ) ) );
        }
        echo '</div>';

        echo '<div class="btn-group pull-right">';
        if( self::$full_time_is == FALSE ) {
            print_small_button( plugin_page( 'calendar_user_page' ) . "&for_user=" . $this->for_user . "&week=" . date( "W", timestamp_previous_week_get( $this->week, $this->year ) ) . "&year=" . date( "o", timestamp_previous_week_get( $this->week, $this->year ) ), plugin_lang_get( 'previous_period' ) );
            print_small_button( plugin_page( 'calendar_user_page' ) . "&for_user=" . $this->for_user . "&week=" . (int)date( "W" ), plugin_lang_get( 'week' ) );
            print_small_button( plugin_page( 'calendar_user_page' ) . "&for_user=" . $this->for_user . "&week=" . date( "W", timestamp_next_week_get( $this->week, $this->year ) ) . "&year=" . date( "o", timestamp_next_week_get( $this->week, $this->year ) ), plugin_lang_get( 'next_period' ) );
        } else {
            print_small_button( plugin_page( 'calendar_user_page' ) . "&for_user=" . $this->for_user . "&week=" . date( "W", timestamp_previous_week_get( $this->week, $this->year ) ) . "&year=" . date( "o", timestamp_previous_week_get( $this->week, $this->year ) ) . "&full_time=TRUE", plugin_lang_get( 'previous_period' ) );
            print_small_button( plugin_page( 'calendar_user_page' ) . "&for_user=" . $this->for_user . "&week=" . (int)date( "W" ) . "&full_time=TRUE", plugin_lang_get( 'week' ) );
            print_small_button( plugin_page( 'calendar_user_page' ) . "&for_user=" . $this->for_user . "&week=" . date( "W", timestamp_next_week_get( $this->week, $this->year ) ) . "&year=" . date( "o", timestamp_next_week_get( $this->week, $this->year ) ) . "&full_time=TRUE", plugin_lang_get( 'next_period' ) );
        }
        echo '</div>';
        
        echo '<div class="btn-group pull-right">';
        echo '<input type="submit" class="btn btn-primary btn-white btn-round btn-sm" value="' . plugin_lang_get( 'goto_date_button' ) . '" />';
        echo '<input type="text" id="date_select" name="date_select" class="datetimepicker input-sm" ' .
                                'data-picker-locale="' . lang_get_current_datetime_locale() .
                                '" data-picker-format="' . plugin_config_get( 'datetime_picker_format' ) . '" ' .
                                'size="10" maxlength="10" autocomplete="off" value="' . $t_date_to_display . '" />';
        echo '</div>';
        
        echo '</form>';
        echo '</div>';
        echo '</div>';        
    }

    protected function print_menu_bottom() {

        echo '<div class="widget-toolbox padding-8 clearfix">';
        echo '<div class="btn-toolbar">';

        echo '<div class="btn-group pull-left">';
        if( access_compare_level( access_get_project_level(), plugin_config_get( 'report_event_threshold' ) ) ) {
            if( self::$full_time_is == TRUE ) {
                print_small_button( plugin_page( 'event_add_page' ) . "&full_time=TRUE", plugin_lang_get( 'add_new_event' ) );
            } else {
                print_small_button( plugin_page( 'event_add_page' ), plugin_lang_get( 'add_new_event' ) );
            }
        }
        echo '</div>';

        echo '<div class="btn-group pull-right">';

        echo '<form id="filter-queries-form" class="form-inline pull-left padding-left-8"  method="get" name="list_queries" action="' . plugin_page( 'calendar_user_page' ) . '">';
        # CSRF protection not required here - form does not result in modifications
        echo '<input type="hidden" name="page" value="Calendar/calendar_user_page" />';
        echo '<input type="hidden" name="week" value="' . $this->week . '" />';
        echo '<input type="hidden" name="year" value="' . $this->year . '" />';
        echo '<input type="hidden" name="full_time" value="' . (int)self::$full_time_is . '" />';

        echo '<label class="inline">' . plugin_lang_get( 'filter_text' ) . '</label>';
        echo '<select name="for_user">';
        echo '<option value="' . auth_get_current_user_id() . '">[' . lang_get( 'reset_query' ) . ']</option>';
        if( $this->for_user == 0 ) {
            echo '<option selected="selected" value="0">[' . plugin_lang_get( 'select_all_users' ) . ']</option>';
        } else {
            echo '<option value="0">[' . plugin_lang_get( 'select_all_users' ) . ']</option>';
        }

        print_user_option_list( $this->for_user, helper_get_current_project(), plugin_config_get( 'report_event_threshold' ) );

        echo '</select>';
        echo '</form>';

        echo '</div>';

        echo '</div>';
        echo '</div>';
    }

}
