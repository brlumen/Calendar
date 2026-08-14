<?php
# Copyright (c) 2025 Grigoriy Ermolaev (igflocal@gmail.com)
# Calendar for MantisBT is free software

/**
 * Get all events for the given date
 * @param string $p_date Date in YYYY-MM-DD format
 * @return array Array of events
 */
function calendar_get_events_for_date($p_date) {
    $t_events_table = plugin_table('events');
    
    $t_timestamp_start = strtotime($p_date . ' 00:00:00');
    $t_timestamp_end = strtotime($p_date . ' 23:59:59');
    
    $t_query = "SELECT * FROM $t_events_table 
                WHERE (date_from BETWEEN " . db_param() . " AND " . db_param() . ")
                OR (date_to BETWEEN " . db_param() . " AND " . db_param() . ")
                OR (date_from <= " . db_param() . " AND date_to >= " . db_param() . ")";
                
    $t_result = db_query($t_query, array(
        $t_timestamp_start,
        $t_timestamp_end,
        $t_timestamp_start,
        $t_timestamp_end,
        $t_timestamp_start,
        $t_timestamp_end
    ));
    
    $t_events = array();
    while ($t_row = db_fetch_array($t_result)) {
        $t_events[] = $t_row;
    }
    
    return $t_events;
} 