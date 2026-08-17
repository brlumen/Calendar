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
 * Public API of the Calendar plugin, meant to be called from other plugins.
 *
 * The contract is the request class of each call, currently
 * \CalendarPluginApi\EventCreateRequest. A caller does not have to declare a
 * hard dependency on Calendar: checking class_exists( 'CalendarPluginApi\\EventCreateRequest' )
 * at runtime is enough to know whether the plugin is installed and loaded.
 *
 * Every function here is a facade and follows the same two rules:
 * - the whole body runs inside plugin_push_current( 'Calendar' ), otherwise
 *   plugin_table() / plugin_config_get() would resolve against the calling
 *   plugin instead of this one;
 * - all input arrives through the request object - no gpc_*, no current user
 *   and no form security here, those belong to the pages/ layer.
 */

/**
 * Create a calendar event on behalf of the user named by the request.
 *
 * The request is validated first, then the referenced project, user, members
 * and issue are checked for existence. Access is checked against the
 * 'report_event_threshold' level of the target project for
 * $p_request->user_id, not for the logged in user.
 *
 * @param \CalendarPluginApi\EventCreateRequest $p_request Event description.
 * @return int Identifier of the created event.
 * @access public
 */
function calendar_api_event_create( \CalendarPluginApi\EventCreateRequest $p_request ) : int {

    plugin_push_current( 'Calendar' );

    try {
        $p_request->validate();

        $t_project_id = $p_request->project_id;
        $t_user_id    = $p_request->user_id;

        project_ensure_exists( $t_project_id );
        user_ensure_exists( $t_user_id );

        if( $p_request->bug_id !== null ) {
            bug_ensure_exists( $p_request->bug_id );
        }

        # the access level is read per user and per project, so that project
        # specific overrides of the threshold are honoured
        $t_threshold = plugin_config_get( 'report_event_threshold', NULL, FALSE, $t_user_id, $t_project_id );

        if( !access_has_project_level( $t_threshold, $t_project_id, $t_user_id ) ) {
            access_denied();
        }

        # an empty member list means the author attends their own event
        $t_members = $p_request->members;
        if( count( $t_members ) == 0 ) {
            $t_members = array( $t_user_id );
        }
        foreach( $t_members as $t_member_id ) {
            user_ensure_exists( (int)$t_member_id );
        }

        $t_recurrence_pattern = trim( $p_request->recurrence_pattern );
        if( !is_blank( $t_recurrence_pattern ) ) {
            try {
                new \RRule\RSet( $t_recurrence_pattern );
            } catch( Exception $e ) {
                error_parameters( 'recurrence_pattern' );
                trigger_error( ERROR_INVALID_FIELD_VALUE, ERROR );
            }
        }

        # an unknown or missing name falls back to the instance timezone, the
        # same way the event creation form behaves
        $t_timezone = calendar_timezone_get( $p_request->timezone );

        $t_event_data = new CalendarEventData();

        $t_event_data->project_id         = $t_project_id;
        $t_event_data->name               = $p_request->name;
        $t_event_data->activity           = 'Y';
        $t_event_data->author_id          = $t_user_id;
        $t_event_data->changed_user_id    = $t_user_id;
        $t_event_data->date_changed       = time();
        $t_event_data->date_from          = $p_request->date_from;
        $t_event_data->date_to            = $p_request->date_to;
        $t_event_data->duration           = $p_request->date_to - $p_request->date_from;
        $t_event_data->recurrence_pattern = $t_recurrence_pattern;
        $t_event_data->timezone           = $t_timezone->getName();

        # create() validates the data and raises the plugin errors itself
        $t_event_id = $t_event_data->create();

        if( $p_request->bug_id !== null ) {
            event_attach_issue( $t_event_id, array( $p_request->bug_id ) );
        }

        foreach( $t_members as $t_member_id ) {
            event_member_add( $t_event_id, (int)$t_member_id, $t_user_id );
        }

        event_google_add( $t_event_id, $t_event_data->author_id, $t_members );

        # EVENT_CALENDAR_EVENT_CREATED is signalled by CalendarEventData::create(),
        # so that subscribers see the events of the pages/ layer as well

        return $t_event_id;
    } finally {
        plugin_pop_current();
    }
}
