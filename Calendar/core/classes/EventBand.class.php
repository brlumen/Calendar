<?php
# Copyright (c) 2026 Grigoriy Ermolaev (igflocal@gmail.com)
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
 * A multi-day occurrence in the week grid: one bar under the day titles,
 * as wide as the columns of the days it runs through, in the lane the
 * WeekCalendar assigned to it. Rendered inside the column of its first day
 * and stretched over the following ones, the same way EventArea is placed
 * inside a day column.
 */
class EventBand {
    private $event;
    private $lane;
    private $span;
    private $is_in_past;

    /**
     * @param array $p_event_row Day row of the occurrence (id, date_from, duration)
     * @param int   $p_lane      Row of the band area the bar is drawn in, from 0
     * @param int   $p_span      Number of day columns the bar covers
     */
    function __construct( $p_event_row, $p_lane, $p_span ) {
        $this->event      = $p_event_row;
        $this->lane       = (int)$p_lane;
        $this->span       = max( 1, (int)$p_span );
        $this->is_in_past = ( $this->event['date_from'] + $this->event['duration'] ) < strtotime( date( 'j.n.Y' ) );
    }

    public function html() {
        $t_name    = event_get_field( $this->event['id'], 'name' );
        $t_project = project_get_field( event_get_field( $this->event['id'], 'project_id' ), 'name' );
        # the bar itself shows which days the occurrence covers, so only the
        # times of its start and end are spelled out
        $t_text    = $t_name . ' | ' . date( 'H:i', $this->event['date_from'] ) . ' - ' . date( 'H:i', $this->event['date_from'] + $this->event['duration'] ) . ' [ ' . $t_project . ' ]';

        $t_top = ColumnForm::HEADER_HEIGHT + $this->lane * ColumnForm::BAND_HEIGHT + 1;
        $t_id  = $this->is_in_past ? 'event_week_expired' : 'event_week';

        # every column adds its own 1px border to the width of the bar
        return '<a href=' . WeekCalendar::$link_options
                . '&event_id=' . $this->event['id']
                . '&date=' . $this->event['date_from']
                . ' id="' . $t_id . '"'
                . ' class="event-band"'
                . ' title="' . string_attribute( $t_text ) . '"'
                . ' style="z-index:' . ( 100 + $this->lane ) . ';'
                . ' top:' . $t_top . 'px;'
                . ' height:' . ( ColumnForm::BAND_HEIGHT - 2 ) . 'px;'
                . ' width: calc(' . $this->span . ' * (100% + 1px) + 1px);">' . $t_text . '</a>';
    }

}
