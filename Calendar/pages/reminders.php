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

# Store the personal reminder settings submitted from reminders_page.php.

auth_ensure_user_authenticated();

current_user_ensure_unprotected();

form_security_validate( 'calendar_reminders_edit' );

if( !calendar_reminder_feature_enabled() ) {
    access_denied();
}

$t_current_user_id = auth_get_current_user_id();

$f_reminders_enabled = gpc_get_bool( 'reminders_enabled' ) ? ON : OFF;

# an empty list is legal and means "nothing, unless the event asks for it"
$t_reminder_offsets = calendar_reminder_offsets_from_input( gpc_get_int_array( 'reminder_value', array() ),
                                                            gpc_get_string_array( 'reminder_unit', array() ) );

plugin_config_set( 'reminders_enabled', $f_reminders_enabled, $t_current_user_id );
plugin_config_set( 'reminders_default', $t_reminder_offsets, $t_current_user_id );

form_security_purge( 'calendar_reminders_edit' );

$t_redirect_url = plugin_page( 'reminders_page', TRUE );

layout_page_header( null, $t_redirect_url );

layout_page_begin( $t_redirect_url );

html_operation_successful( $t_redirect_url );

layout_page_end();
