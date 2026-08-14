<?php

/**
 * Convert a Unix timestamp to a DateTime carrying the current timezone,
 * for use as DTSTART/UNTIL of an RRULE. An integer timestamp would be
 * treated as UTC by php-rrule, freezing the UTC time of occurrences and
 * shifting their local time whenever DST starts or ends (issue #104);
 * a DateTime with a named timezone keeps the local time constant instead.
 *
 * @param integer $p_timestamp
 * @return DateTime
 */
function calendar_rrule_datetime( $p_timestamp ) {
    $t_datetime = new DateTime( '@' . (int)$p_timestamp );
    return $t_datetime->setTimezone( new DateTimeZone( date_default_timezone_get() ) );
}

function helper_ensure_event_update_confirmed( $p_message ) {
    if( true == gpc_get_string( '_confirmed', FALSE ) ) {
        return gpc_get_string( '_confirmed' );
    }

    layout_page_header();
    layout_page_begin();

    echo '<div class="col-md-12 col-xs-12">';
    echo '<div class="space-10"></div>';
    echo '<div class="alert alert-warning center">';
    echo '<p class="bigger-110">';
    echo "\n" . $p_message . "\n";
    echo '</p>';
    echo '<div class="space-10"></div>';

    echo '<form method="post" class="center" action="">' . "\n";
    # CSRF protection not required here - user needs to confirm action
    # before the form is accepted.
    print_hidden_inputs( $_POST );
    print_hidden_inputs( $_GET );

    echo '<input type="hidden" name="_confirmed" value="THIS" />', "\n";
    echo '<input type="submit" class="btn btn-primary btn-white btn-round" value="' . plugin_lang_get( 'this_event' ) . '" />';
    echo "\n</form>";

    echo '<form method="post" class="center" action="">' . "\n";
    # CSRF protection not required here - user needs to confirm action
    # before the form is accepted.
    print_hidden_inputs( $_POST );
    print_hidden_inputs( $_GET );

    echo '<input type="hidden" name="_confirmed" value="THISANDFUTURE" />', "\n";
    echo '<input type="submit" class="btn btn-primary btn-white btn-round" value="' . plugin_lang_get( 'this_and_future_event' ) . '" />';
    echo "\n</form>";

    echo '<form method="post" class="center" action="">' . "\n";
    # CSRF protection not required here - user needs to confirm action
    # before the form is accepted.
    print_hidden_inputs( $_POST );
    print_hidden_inputs( $_GET );

    echo '<input type="hidden" name="_confirmed" value="ALL" />', "\n";
    echo '<input type="submit" class="btn btn-primary btn-white btn-round" value="' . plugin_lang_get( 'all_event' ) . '" />';
    echo "\n</form>\n";

    echo '<div class="space-10"></div>';
    echo '</div></div>';

    layout_page_end();
    exit;
}
