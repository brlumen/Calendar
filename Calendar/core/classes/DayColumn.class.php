<?php

class DayColumn extends ColumnForm {
    protected $timestamp          = 1;
    protected $events_area_group  = array();
    protected $is_today;
    protected $event_above_count = 0;
    protected $event_below_count = 0;

    /**
     * @param int    $p_timestamp     Day start
     * @param array  $p_events_row    Events of the day
     * @param string $p_full_time_url URL of the same view in 0-24 mode; NULL leaves the
     *                                out-of-range counters as plain text
     */
    public function __construct( $p_timestamp, $p_events_row, $p_full_time_url = NULL ) {
        parent::__construct();

        $this->timestamp = $p_timestamp;

        $t_events_row_to_group = array();

        foreach( $p_events_row as $t_event_row ) {
            $t_time_ar         = explode( ":", date( "H:i", $t_event_row['date_from'] ) );
            $t_event_timestamp = ((int)$t_time_ar[0] * 3600) + ((int)$t_time_ar[1] * 60);
            if( $t_event_timestamp < self::$time_period_list[0] ) {
                $this->event_above_count++;
            } elseif( $t_event_timestamp >= self::$time_period_list[count( self::$time_period_list ) - 1] ) {
                $this->event_below_count++;
            } else {
                $t_events_row_to_group[] = $t_event_row;
            }
        }

        $t_group_number     = 0;
        $t_group_events_row = array();

        foreach( $t_events_row_to_group as $key => &$t_event_row ) {
            unset( $t_events_row_to_group[$key] );
            $this->group_event( $t_event_row, $t_events_row_to_group, $t_group_events_row[$t_group_number] );
            $t_group_number++;
            reset( $t_events_row_to_group );
        }

        foreach( $t_group_events_row as $key => $t_events_row ) {
            foreach( $t_events_row as $key_in_group => $t_event_row ) {
                $this->events_area_group[$key][] = new EventArea( $t_event_row, count( $t_events_row ), $key_in_group );
//                $this->events_area_group[$key][] = CalendarServiceLocator::get( 'EventArea', array( $t_event_row, count( $t_events_row ), $key_in_group ));
            }
        }

        $this->title_text = plugin_lang_get( date( "D", $this->timestamp ) ) . ', ' . date( config_get( 'short_date_format' ), $this->timestamp );
        if( $this->event_above_count > 0 ) {
            $this->first_row_text = $this->out_of_range_html( $this->event_above_count, 'out_of_range_above', $p_full_time_url );
        }
        if( $this->event_below_count > 0 ) {
            $this->last_row_text = $this->out_of_range_html( $this->event_below_count, 'out_of_range_below', $p_full_time_url );
        }

        $this->is_today = date( "U", strtotime( date( "j.n.Y" ) ) ) == $this->timestamp ? TRUE : FALSE;
        self::$total_days_counter++;
    }

//    public static function get_event_area( $p_event_row, $p_total_event_in_group, $p_current_number_in_group ) {
//        return new EventArea( $p_event_row, $p_total_event_in_group, $p_current_number_in_group );
//    }

    /**
     * "+N events earlier/later" counter, linked to the 0-24 view when a URL is given
     */
    private function out_of_range_html( $p_count, $p_lang_key, $p_full_time_url ) {
        $t_text = '+' . $p_count . ' ' . plugin_lang_get( $p_lang_key );
        if( $p_full_time_url === NULL ) {
            return $t_text;
        }

        return '<a href="' . string_attribute( $p_full_time_url ) . '">' . $t_text . '</a>';
    }

    protected function html_column_param() {
        $t_class = $this->is_today ? 'column-this-day-td' : 'column-day-td';

        return '<td class="' . $t_class . '"'
                . ' data-date="' . date( 'Y-m-d', $this->timestamp ) . '"'
                . ' style="width: calc(100%/' . self::$total_days_counter . ')">';
    }

    protected function html_hour_li_attr( $p_time ) {
        return ' class="time-slot" data-time="' . (int)$p_time . '"';
    }

    protected function html_body() {
        $t_result = '';

        foreach( $this->events_area_group as $t_events_area ) {
            foreach( $t_events_area as $t_event_area ) {
                $t_result .= $t_event_area->html();
            }
        }

        return $t_result;
    }

    protected function group_event( $p_event_first, &$p_events, &$p_result ) {
        foreach( $p_events as $key => &$t_event ) {
            if( $p_event_first['date_from'] >= $t_event['date_from'] && $p_event_first['date_from'] < $t_event['date_from'] + $t_event['duration'] || $p_event_first['date_from'] + $p_event_first['duration'] > $t_event['date_from'] && $p_event_first['date_from'] + $p_event_first['duration'] < $t_event['date_from'] + $t_event['duration'] || $p_event_first['date_from'] < $t_event['date_from'] && $p_event_first['date_from'] + $p_event_first['duration'] >= $t_event['date_from'] + $t_event['duration']
            ) {
                unset( $p_events[$key] );
                $this->group_event( $t_event, $p_events, $p_result );
            }
        }
        $p_result[] = $p_event_first;
    }

}
