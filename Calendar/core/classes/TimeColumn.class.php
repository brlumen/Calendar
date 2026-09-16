<?php
# Copyright (c) 2026 Grigoriy Ermolaev (igflocal@gmail.com)
# Calendar plugin for MantisBT is free software:
# you can redistribute it and/or modify it under the terms of the GNU
# General Public License as published by the Free Software Foundation,
# either version 2 of the License, or (at your option) any later version.
#
# Calendar plugin for MantisBT is distributed in the hope
# that it will be useful, but WITHOUT ANY WARRANTY; without even the
# implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Calendar plugin for MantisBT.
# If not, see <http://www.gnu.org/licenses/>.

/**
 * Description of ColumnTime
 *
 * @author ermolaev
 */
class TimeColumn extends ColumnForm {

    function __construct() {
        parent::__construct();
        $this->title_text    = plugin_lang_get( 'time_event' );
        $t_date              = self::$time_period_list[count( self::$time_period_list ) - 1];
        $this->last_row_text = gmdate( "H:i", $t_date );
    }

    protected function html_column_param() {
        return '<td class="column-time-td">';
    }

    protected function html_hour_text( $p_time ) {
        $t_result = '';

//        if( $p_time % (Calendar::$min_segment_time_in_hour * 2) == 0 ) {
//            $t_result .= gmdate( "H:i", $p_time );
//        }
        if( self::$intervals_per_hour % 2 == 0 ) {
            if( ( $p_time / self::$min_segment_time_in_hour ) % 2 == 0 ) {
                $t_result .= gmdate( "H:i", $p_time );
            }
        } elseif( ( $p_time / self::$min_segment_time_in_hour ) % 2 != 0 ) {
            $t_result .= gmdate( "H:i", $p_time );
        }
        return $t_result;
    }

}
