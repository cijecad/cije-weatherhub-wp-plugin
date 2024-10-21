<?php
// Function to handle posting weather data
function post_weather_data() {
    global $wpdb;

    // Get POST data
    $station_id = isset($_POST['station_id']) ? sanitize_text_field($_POST['station_id']) : null;
    $passkey = isset($_POST['passkey']) ? sanitize_text_field($_POST['passkey']) : null;
    $temperature = isset($_POST['temperature']) ? floatval($_POST['temperature']) : null;
    $humidity = isset($_POST['humidity']) ? floatval($_POST['humidity']) : null;
    $pressure = isset($_POST['pressure']) ? floatval($_POST['pressure']) : null;
    $wind_speed = isset($_POST['wind_speed']) ? floatval($_POST['wind_speed']) : null;

    // Validate station_id and passkey
    if ($station_id && $passkey) {
        // Query to select station_name and passkey
        $stations_table = $wpdb->prefix . 'weather_stations';
        $query = $wpdb->prepare("SELECT station_name, passkey FROM $stations_table WHERE station_id = %s", $station_id);
        $row = $wpdb->get_row($query);

        if ($row) {
            $db_passkey = $row->passkey;
            $station_name = $row->station_name;

            // Check if the passkey matches
            if ($db_passkey !== $passkey) {
                wp_send_json_error('Invalid station_id or passkey');
                wp_die();
            }

            // Insert weather data into the database with station_name
            $data_table = $wpdb->prefix . 'weather_data';
            $inserted = $wpdb->insert(
                $data_table,
                array(
                    'station_id'  => $station_id,
                    'temperature' => $temperature,
                    'humidity'    => $humidity,
                    'pressure'    => $pressure,
                    'wind_speed'  => $wind_speed,
                    'datetime'    => current_time('mysql'),
                ),
                array('%s', '%f', '%f', '%f', '%f', '%s')
            );

            if ($inserted) {
                wp_send_json_success('Data posted successfully');
            } else {
                wp_send_json_error('Failed to insert data');
            }
        } else {
            wp_send_json_error('Station not found');
        }
    } else {
        wp_send_json_error('Missing station_id or passkey');
    }

    wp_die(); // Required to terminate AJAX request properly
}

// Handle AJAX request for posting weather data
add_action('wp_ajax_post_weather_data', 'post_weather_data');
add_action('wp_ajax_nopriv_post_weather_data', 'post_weather_data');
?>