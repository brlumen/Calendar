<?php
# Copyright (c) 2018 Grigoriy Ermolaev (igflocal@gmail.com)
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

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

layout_page_header( plugin_lang_get( 'name_plugin_description_page' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_plugin_page.php' );
calendar_print_manage_config_menu( plugin_page( 'config_page' ) );

$t_name_days_week = plugin_config_get( 'arWeekdaysName' );
?>

<div class="col-md-12 col-xs-12">
    <div class="space-10"></div>
    <div class="form-container">
        <form action="<?php echo plugin_page( 'config' ) ?>" method="post" enctype="multipart/form-data"> 
            <?php echo form_security_field( 'config' ) ?>
            <div class="widget-box widget-color-blue2">
                <div class="widget-header widget-header-small">
                    <h4 class="widget-title lighter">
                        <i class="ace-icon fa fa-cubes"></i>
                        <?php echo plugin_lang_get( 'name_plugin_description_page' ) . ': ' . plugin_lang_get( 'config_title' ) ?>
                    </h4>
                </div>

                <div class="widget-body">
                    <div class="widget-main no-padding">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-condensed table-hover">
                                <colgroup>
                                    <col style="width:25%" />
                                    <col style="width:25%" />
                                    <col style="width:25%" />
                                </colgroup>

                                <tr>
                                    <td class="category" width="50%">
                                        <?php echo plugin_lang_get( 'config_days_week_display' ) ?>

                                    </td>

                                    <td colspan="3" width="50%">
                                        <?php
                                        foreach( $t_name_days_week as $t_name_day => $t_status ) {
                                            if( $t_status == ON ) {
                                                echo '<label><input type="checkbox" name="days_week[]" value="' . $t_name_day . '" checked="checked">' . plugin_lang_get( $t_name_day ) . '</input></label><br>';
                                            } else {
                                                echo '<label><input type="checkbox" name="days_week[]" value="' . $t_name_day . '">' . plugin_lang_get( $t_name_day ) . '</input></label><br>';
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="category" width="50%">
                                        <?php echo plugin_lang_get( 'config_time_day_range' ) ?>

                                    </td>

                                    <td class="center" width="25%">
                                        <select name="time_day_start">
                                            <?php
                                            $t_time_day_start = plugin_config_get( 'time_day_start' );

                                            print_time_select_option( $t_time_day_start, TRUE );
                                            ?>
                                        </select>
                                    </td>
                                    <td class="center" width="25%">
                                        <select name="time_day_finish">
                                            <?php
                                            $t_time_day_finish = plugin_config_get( 'time_day_finish' );

                                            print_time_select_option( $t_time_day_finish, TRUE );
                                            ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="category" width="50%">
                                        <?php echo plugin_lang_get( 'config_reminders_feature_enabled' ) ?>

                                    </td>

                                    <td colspan="3" width="50%">
                                        <?php
                                        $t_reminders_feature_enabled = plugin_config_get( 'reminders_feature_enabled' ) == ON;

                                        echo '<label><input type="checkbox" name="reminders_feature_enabled" value="1"'
                                                . ( $t_reminders_feature_enabled ? ' checked="checked"' : '' ) . '></input></label>';

                                        # how timely a reminder is depends entirely on the core cron job,
                                        # so its state is reported right where the feature is switched on
                                        if( $t_reminders_feature_enabled ) {

                                            $t_reminder_last_cron_run = (int)plugin_config_get( 'reminder_last_cron_run' );

                                            # the command of this very installation is spelled out next to the
                                            # documentation link, so that scheduling it needs no path guessing
                                            $t_reminder_cron_command = '<code>php ' . string_display_line( config_get_global( 'absolute_path' ) ) . 'scripts/cronjob.php</code>';
                                            $t_reminder_cron_doc     = '<a href="https://mantisbt.org/docs/master/en-US/Admin_Guide/html-desktop/#admin.config.email" target="_blank" rel="noopener">'
                                                    . plugin_lang_get( 'reminder_cron_doc_link' ) . '</a>';

                                            if( $t_reminder_last_cron_run == 0 ) {
                                                echo '<div class="alert alert-warning">' . sprintf( plugin_lang_get( 'reminder_warning_cron_never' ), $t_reminder_cron_command, $t_reminder_cron_doc ) . '</div>';
                                            } else {
                                                $t_reminder_last_cron_run_text = date( config_get( 'normal_date_format' ), $t_reminder_last_cron_run );

                                                if( time() - $t_reminder_last_cron_run > 3600 ) {
                                                    echo '<div class="alert alert-warning">' . sprintf( plugin_lang_get( 'reminder_warning_cron_stale' ), $t_reminder_last_cron_run_text, $t_reminder_cron_command, $t_reminder_cron_doc ) . '</div>';
                                                } else {
                                                    echo '<div class="alert alert-success">' . sprintf( plugin_lang_get( 'reminder_cron_ok' ), $t_reminder_last_cron_run_text ) . '</div>';
                                                }
                                            }

                                            if( config_get( 'email_send_using_cronjob' ) == ON ) {
                                                echo '<div class="alert alert-info">' . plugin_lang_get( 'reminder_notice_send_emails_cron' ) . '</div>';
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="category" width="50%">
                                        <?php echo plugin_lang_get( 'config_notifications_feature_enabled' ) ?>

                                    </td>

                                    <td colspan="3" width="50%">
                                        <?php
                                        echo '<label><input type="checkbox" name="notifications_feature_enabled" value="1"'
                                                . ( plugin_config_get( 'notifications_feature_enabled' ) == ON ? ' checked="checked"' : '' ) . '></input></label>';

                                        # who is mailed about what is a matrix of its own, and it can be
                                        # filled in before the feature is switched on
                                        echo '<div class="space-4"></div>';
                                        echo '<a href="' . plugin_page( 'notify_config_page' ) . '">'
                                                . plugin_lang_get( 'notify_config_link' ) . '</a>';
                                        ?>
                                    </td>
                                </tr>

                                <?php
                                $t_google_client_id = json_decode( plugin_config_get( 'google_client_secret' ), TRUE );
                                if( is_array( $t_google_client_id ) && !empty( $t_google_client_id['web']['client_id'] ) ) {
                                    ?>

                                    <tr>
                                        <td class="category" width="50%">
                                            <?php echo plugin_lang_get( 'config_page_google_api_settings' ) ?>
                                        </td>

                                        <td colspan="2">

                                            <div class = "fallback">
                                                <pre>
                                                    <?php
//                                                echo plugin_lang_get( 'config_page_google_client_id' ) . ': ' . $t_google_client_id['web']['client_id'];
                                                    print_r( $t_google_client_id['web'] );
//                                                echo '</br>';
//                                                foreach( $t_google_client_id['web']['redirect_uris'] as $t_url ) {
//                                                    echo $t_url;
//                                                    echo '</br>';
//                                                }
                                                    ?>
                                                </pre>
                                            </div>

                                        </td>
                                    </tr>
                                <?php } ?>

                                <tr>
                                    <td class="category" width="50%">
                                        <?php echo sprintf( plugin_lang_get( 'config_google_api_file' ), config_get_global( 'path' ) . plugin_page( 'user_config_google', TRUE ) ) ?>
                                    </td>

                                    <td class="center" colspan="2">

                                        <div class = "fallback">
                                            <input style="display: inline" id="ufile" name="ufile" type="file" size="15" accept="application/json"/>
                                        </div>

                                    </td>
                                </tr>

                                <tr>
                                    <td class="center" colspan="3">
                                        <input type="submit" class="button" value="<?php echo lang_get( 'change_configuration' ) ?>" />
                                    </td>
                                </tr>

                            </table>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
if( !function_exists( 'html_page_bottom' ) ) {
    layout_page_end();
} else {
    html_page_bottom();
}
