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

function print_time_select_option( $p_selected_time = NULL, $p_full_range = FALSE ) {

    if( $p_full_range == FALSE ) {
        $t_time_day_start_timestamp  = plugin_config_get( 'time_day_start', plugin_config_get( 'time_day_start' ), FALSE, auth_get_current_user_id() );
        $t_time_day_finish_timestamp = plugin_config_get( 'time_day_finish', plugin_config_get( 'time_day_finish' ), FALSE, auth_get_current_user_id() );
    } else {
        $t_time_day_start_timestamp  = 0;
        $t_time_day_finish_timestamp = 86400;
    }

    if( $p_selected_time < $t_time_day_start_timestamp && $p_selected_time !== NULL || $p_selected_time > $t_time_day_finish_timestamp && $p_selected_time !== NULL ) {
        $t_time_day_start_timestamp  = 0;
        $t_time_day_finish_timestamp = 86400;
    }

    $t_time_count          = 3600 / plugin_config_get( 'stepDayMinutesCount' );
    $t_select_time_options = range( $t_time_day_start_timestamp, $t_time_day_finish_timestamp, $t_time_count );

    echo '<option value="--:--">--:--</option>';
    foreach( $t_select_time_options as $key => $t_current_time ) {

        if( $p_selected_time !== $t_current_time ) {
            echo '<option value="' . $t_current_time . '">' . gmdate( "H:i", $t_current_time ) . '</option>';
        } else {
            echo '<option selected value="' . $t_current_time . '">' . gmdate( "H:i", $t_current_time ) . '</option>';
        }
    }
}

/**
 * Print the editor of the reminder offsets, shared by the event pages and by
 * the user settings page. Every label is rendered here, so that the script
 * cloning the rows needs no language strings of its own.
 * @param array   $p_offsets   Offsets in seconds to prefill the rows with.
 * @param boolean $p_show_hint Whether to explain what an empty list means.
 * @return void
 * @access public
 */
function print_event_reminder_rows( array $p_offsets, $p_show_hint = true, $p_show_disable = false ) {

    $t_max_rows = (int)plugin_config_get( 'reminder_max_per_event' );
    $t_disabled = calendar_reminder_offsets_disabled( $p_offsets );

    # switching reminders off is a third state next to "own reminders" and
    # "none set": without it an empty editor always falls back to the personal
    # defaults of the members
    if( $p_show_disable ) {
        echo '<div class="calendar-reminder-disable">';
        echo '<label><input type="checkbox" id="calendar-reminder-disabled" name="reminders_disabled" value="1"'
                . ( $t_disabled ? ' checked="checked"' : '' ) . ' /> '
                . plugin_lang_get( 'reminder_disable_label' ) . '</label>';
        echo '</div>';
        echo '<div class="space-4"></div>';
    }

    echo '<div id="calendar-reminder-rows" data-max-rows="' . $t_max_rows . '">';

    foreach( $p_offsets as $t_offset ) {

        if( (int)$t_offset === CALENDAR_REMINDER_DISABLED ) {
            continue;
        }

        print_event_reminder_row( calendar_reminder_offset_to_input( $t_offset ), FALSE );
    }

    # the hidden last row is the template the script clones; its fields are
    # disabled, so the template itself is never submitted
    print_event_reminder_row( array( 'value' => 10, 'unit' => 'minutes' ), TRUE );

    echo '</div>';

    echo '<button type="button" id="calendar-reminder-add" class="btn btn-sm btn-primary btn-white btn-round">'
            . '<i class="ace-icon fa fa-plus"></i> ' . plugin_lang_get( 'reminder_add_row' ) . '</button>';

    if( $p_show_hint ) {
        echo '<div class="space-4"></div>';
        echo '<span class="small">' . plugin_lang_get( 'reminder_hint_defaults' ) . '</span>';
    }
}

/**
 * Print one row of the reminder editor
 * @param array   $p_input       Row as returned by calendar_reminder_offset_to_input().
 * @param boolean $p_is_template Whether this is the hidden row cloned by the script.
 * @return void
 * @access public
 */
function print_event_reminder_row( array $p_input, $p_is_template ) {

    $t_disabled = $p_is_template ? ' disabled="disabled"' : '';
    $t_class    = $p_is_template ? 'calendar-reminder-row calendar-reminder-template' : 'calendar-reminder-row';
    $t_style    = $p_is_template ? ' style="display: none;"' : '';

    echo '<div class="' . $t_class . '"' . $t_style . '>';

    echo '<input style="width: 70px;" type="number" class="input-sm" name="reminder_value[]" min="1" step="1" value="'
            . (int)$p_input['value'] . '"' . $t_disabled . ' /> ';

    echo '<select class="input-sm" name="reminder_unit[]"' . $t_disabled . '>';
    foreach( array_keys( calendar_reminder_units() ) as $t_unit ) {
        echo '<option value="' . $t_unit . '"' . ( $p_input['unit'] == $t_unit ? ' selected="selected"' : '' ) . '>'
                . plugin_lang_get( 'reminder_unit_' . $t_unit ) . '</option>';
    }
    echo '</select> ';

    echo '<button type="button" class="btn btn-xs btn-danger btn-white btn-round calendar-reminder-remove" title="'
            . plugin_lang_get( 'reminder_remove_row' ) . '"><i class="ace-icon fa fa-trash-o"></i></button>';

    echo '</div>';
}
