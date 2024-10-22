<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

function fetch_latest_weather_data() {
    global $wpdb;
    $weather_data_table = $wpdb->prefix . 'weather_data';
    $stations_table = $wpdb->prefix . 'weather_stations';

    $query = "
        SELECT wd1.*, ws.station_name, ws.school, ws.latitude, ws.longitude
        FROM $weather_data_table wd1
        INNER JOIN (
            SELECT station_id, MAX(date_time) as latest
            FROM $weather_data_table
            GROUP BY station_id
        ) wd2
        ON wd1.station_id = wd2.station_id AND wd1.date_time = wd2.latest
        LEFT JOIN $stations_table ws ON wd1.station_id = ws.station_id
        ORDER BY wd1.date_time DESC
    ";

    $results = $wpdb->get_results($query);

    if ($results) {
        wp_send_json_success($results);
    } else {
        wp_send_json_error('No data found');
    }

    wp_die(); // Required to terminate AJAX request properly
}

// Handle AJAX request for fetching latest weather data
add_action('wp_ajax_fetch_latest_weather_data', 'fetch_latest_weather_data');
add_action('wp_ajax_nopriv_fetch_latest_weather_data', 'fetch_latest_weather_data');
?>