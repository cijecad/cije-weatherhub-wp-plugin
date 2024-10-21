<?php
// Function to record weather data
define('DOING_AJAX', true);
function record_weather_data($station_id, $temperature, $humidity, $pressure, $wind_speed) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_data';

    // Insert the new weather data into the database
    $wpdb->insert(
        $table_name,
        array(
            'station_id'  => $station_id,
            'temperature' => $temperature,
            'humidity'    => $humidity,
            'pressure'    => $pressure,
            'wind_speed'  => $wind_speed,
            'datetime'    => current_time('mysql'),
        ),
        array(
            '%s', '%f', '%f', '%f', '%f', '%s'
        )
    );

    if ($wpdb->last_error) {
        echo 'Error: ' . $wpdb->last_error;
    } else {
        echo 'Weather data recorded successfully.';
    }
}

// Handle AJAX request for recording weather data
function handle_weather_data_request() {
    if (isset($_POST['station_id'], $_POST['temperature'], $_POST['humidity'], $_POST['pressure'], $_POST['wind_speed'])) {
        $station_id = sanitize_text_field($_POST['station_id']);
        $temperature = floatval($_POST['temperature']);
        $humidity = floatval($_POST['humidity']);
        $pressure = floatval($_POST['pressure']);
        $wind_speed = floatval($_POST['wind_speed']);

        record_weather_data($station_id, $temperature, $humidity, $pressure, $wind_speed);
    } else {
        echo 'Error: Missing required fields.';
    }

    wp_die(); // Required to terminate AJAX request properly
}
add_action('wp_ajax_fetch_weather_data', 'handle_weather_data_request');
add_action('wp_ajax_nopriv_fetch_weather_data', 'handle_weather_data_request');
?>