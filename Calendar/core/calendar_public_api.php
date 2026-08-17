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
 * The request is validated first, then everything it refers to is checked,
 * so that a caller may hand over raw input without knowing the rules of the
 * calendar. The call guarantees that:
 * - the project, the author, every member and the issue exist;
 * - the author passes the 'report_event_threshold' level of the project;
 * - the author is allowed to see the issue the event is attached to, so that
 *   attaching an issue can never disclose one;
 * - every member reaches the project at the 'view_event_threshold' level and
 *   none of them is the anonymous account;
 * - the author passes 'member_add_others_event_threshold' as soon as the
 *   member list names somebody other than the author.
 * All of these run before the event row is written, so a rejected request
 * leaves no orphan event behind. Access is always checked for
 * $p_request->user_id, never for the logged in user.
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

        $t_bug_id = $p_request->bug_id;

        if( $t_bug_id !== null ) {
            bug_ensure_exists( $t_bug_id );
        }

        # the access level is read per user and per project, so that project
        # specific overrides of the threshold are honoured
        $t_threshold = plugin_config_get( 'report_event_threshold', NULL, FALSE, $t_user_id, $t_project_id );

        if( !access_has_project_level( $t_threshold, $t_project_id, $t_user_id ) ) {
            access_denied();
        }

        # the issue selector of the event form is filled by filter_get_bug_rows(),
        # i.e. it only offers issues the user may read - attaching an issue must
        # not become a way around that, so the author has to pass the issue's own
        # view threshold, read in the project the issue lives in
        if( $t_bug_id !== null ) {
            $t_bug_project_id     = bug_get_field( $t_bug_id, 'project_id' );
            $t_view_bug_threshold = config_get( 'view_bug_threshold', NULL, $t_user_id, $t_bug_project_id );

            if( !access_has_bug_level( $t_view_bug_threshold, $t_bug_id, $t_user_id ) ) {
                access_denied();
            }
        }

        # an empty member list means the author attends their own event
        $t_members = $p_request->members;
        if( count( $t_members ) == 0 ) {
            $t_members = array( $t_user_id );
        }

        # the member selector of the event form is filled from
        # project_get_all_user_rows(), so only users who can reach the project of
        # the event are eligible; an ineligible member is a malformed request
        # rather than a permission problem of the author, hence the field error
        $t_adds_other_members = FALSE;

        foreach( $t_members as $t_member_id ) {
            $c_member_id = (int)$t_member_id;

            user_ensure_exists( $c_member_id );

            $t_member_threshold = plugin_config_get( 'view_event_threshold', NULL, FALSE, $c_member_id, $t_project_id );

            if( user_is_anonymous( $c_member_id )
                    || !access_has_project_level( $t_member_threshold, $t_project_id, $c_member_id ) ) {
                error_parameters( 'members' );
                trigger_error( ERROR_INVALID_FIELD_VALUE, ERROR );
            }

            if( $c_member_id != $t_user_id ) {
                $t_adds_other_members = TRUE;
            }
        }

        # signing somebody else up for an event is a separate permission
        if( $t_adds_other_members ) {
            $t_add_others_threshold = plugin_config_get( 'member_add_others_event_threshold', NULL, FALSE, $t_user_id, $t_project_id );

            if( !access_has_project_level( $t_add_others_threshold, $t_project_id, $t_user_id ) ) {
                access_denied();
            }
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

        if( $t_bug_id !== null ) {
            event_attach_issue( $t_event_id, array( $t_bug_id ) );
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

/**
 * Users the given user may sign up as members of an event in the given project.
 *
 * This is the read-only counterpart of the member rules enforced by
 * calendar_api_event_create(): feeding any subset of the returned ids into
 * EventCreateRequest::$members is guaranteed to pass them. It exists so that a
 * caller can offer a member picker without reimplementing the rules, and it
 * never raises an error - an unknown project, an unknown user or a user with
 * no access at all simply yields an empty array.
 *
 * The result is a plain list of user ids, the author included when eligible.
 * A user who may not add others gets back only themselves, which is exactly
 * the member list they are allowed to create an event with.
 *
 * @param int $p_project_id Project the event would belong to.
 * @param int $p_user_id    User the event would be created on behalf of.
 * @return array List of user identifiers, may be empty.
 * @access public
 */
function calendar_api_candidate_members( int $p_project_id, int $p_user_id ) : array {

    plugin_push_current( 'Calendar' );

    try {
        if( !project_exists( $p_project_id ) || !user_exists( $p_user_id ) ) {
            return array();
        }

        $t_self_threshold = plugin_config_get( 'view_event_threshold', NULL, FALSE, $p_user_id, $p_project_id );
        $t_self_eligible  = !user_is_anonymous( $p_user_id )
                && access_has_project_level( $t_self_threshold, $p_project_id, $p_user_id );

        $t_add_others_threshold = plugin_config_get( 'member_add_others_event_threshold', NULL, FALSE, $p_user_id, $p_project_id );

        if( !access_has_project_level( $t_add_others_threshold, $p_project_id, $p_user_id ) ) {
            return $t_self_eligible ? array( $p_user_id ) : array();
        }

        # same source as the member selector of the event creation form
        $t_project_users = project_get_all_user_rows( $p_project_id );
        $t_candidates    = array();

        foreach( $t_project_users as $t_project_user ) {
            $c_candidate_id = (int)$t_project_user['id'];

            if( user_is_anonymous( $c_candidate_id ) ) {
                continue;
            }

            $t_candidate_threshold = plugin_config_get( 'view_event_threshold', NULL, FALSE, $c_candidate_id, $p_project_id );

            if( !access_has_project_level( $t_candidate_threshold, $p_project_id, $c_candidate_id ) ) {
                continue;
            }

            $t_candidates[] = $c_candidate_id;
        }

        return $t_candidates;
    } finally {
        plugin_pop_current();
    }
}
