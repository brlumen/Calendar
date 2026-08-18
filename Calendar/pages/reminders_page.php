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

# Personal reminder settings, a tab of the account section. Anyone who can be
# a member of an event has to reach this page, so it is not behind any of the
# calendar thresholds - it only ever touches the settings of the current user.

auth_ensure_user_authenticated();

current_user_ensure_unprotected();

if( !calendar_reminder_feature_enabled() ) {
    access_denied();
}

$t_current_user_id = auth_get_current_user_id();

layout_page_header( plugin_lang_get( 'reminders_account_tab' ) );

layout_page_begin( 'account_page.php' );

print_account_menu( plugin_page( 'reminders_page', TRUE ) );
?>

<div class="col-md-12 col-xs-12">
    <div class="space-10"></div>
    <div class="form-container">
        <form action="<?php echo plugin_page( 'reminders' ) ?>" method="post">
            <?php echo form_security_field( 'calendar_reminders_edit' ) ?>
            <div class="widget-box widget-color-blue2">
                <div class="widget-header widget-header-small">
                    <h4 class="widget-title lighter">
                        <i class="ace-icon fa fa-bell"></i>
                        <?php echo plugin_lang_get( 'reminders_title' ) ?>
                    </h4>
                </div>

                <div class="widget-body">
                    <div class="widget-main no-padding">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-condensed table-hover">
                                <colgroup>
                                    <col style="width:50%" />
                                    <col style="width:50%" />
                                </colgroup>

                                <tr>
                                    <td class="category">
                                        <?php echo plugin_lang_get( 'reminders_pref_enabled' ) ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo '<label><input type="checkbox" name="reminders_enabled" value="1"'
                                                . ( calendar_reminder_user_enabled( $t_current_user_id ) ? ' checked="checked"' : '' ) . '></input></label>';
                                        ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="category">
                                        <?php echo plugin_lang_get( 'reminders_pref_default' ) ?>
                                    </td>

                                    <td>
                                        <?php print_event_reminder_rows( calendar_reminder_user_offsets( $t_current_user_id ), FALSE ) ?>
                                        <p class="small"><?php echo plugin_lang_get( 'reminders_pref_hint' ) ?></p>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="center" colspan="2">
                                        <input type="submit" class="button" value="<?php echo lang_get( 'change_configuration' ) ?>" />
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
layout_page_end();
