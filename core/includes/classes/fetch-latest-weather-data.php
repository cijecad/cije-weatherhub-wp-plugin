<?php
// Function to fetch the latest weather data for each station
function fetch_latest_weather_data() {
    global $wpdb;

    // Query to fetch the latest weather data for each station, including station details
    $data_table = $wpdb->prefix . 'weather_data';
    $stations_table = $wpdb->prefix . 'weather_stations';

    $query = "
        SELECT wd1.*, ws.station_name, ws.latitude, ws.longitude
        FROM $data_table wd1
        INNER JOIN (
            SELECT station_id, MAX(datetime) as latest
            FROM $data_table
            GROUP BY station_id
        ) wd2
        ON wd1.station_id = wd2.station_id AND wd1.datetime = wd2.latest
        LEFT JOIN $stations_table ws ON wd1.station_id = ws.station_id
        ORDER BY wd1.datetime DESC
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