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
    $precipitation = isset($_POST['precipitation']) ? floatval($_POST['precipitation']) : null;
    $wind_speed = isset($_POST['wind_speed']) ? floatval($_POST['wind_speed']) : null;

    // Log received data for debugging
    error_log('Received data: ' . print_r($_POST, true));

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
                error_log('Invalid passkey'); // Log error
                wp_send_json_error(array('message' => 'Invalid passkey'));
                wp_die();
            }

            // Insert weather data into the database
            $data_table = $wpdb->prefix . 'weather_data';
            $inserted = $wpdb->insert(
                $data_table,
                array(
                    'station_id' => $station_id,
                    'temperature' => $temperature,
                    'humidity' => $humidity,
                    'pressure' => $pressure,
                    'precipitation' => $precipitation,
                    'wind_speed' => $wind_speed
                ),
                array(
                    '%d', '%f', '%f', '%f', '%f', '%f'
                )
            );

            if ($inserted) {
                error_log('Data inserted successfully'); // Log success
                wp_send_json_success(array('message' => 'Weather data successfully posted'));
            } else {
                error_log('Failed to insert data: ' . $wpdb->last_error); // Log error
                wp_send_json_error(array('message' => 'Failed to post weather data'));
            }
        } else {
            error_log('Invalid station_id'); // Log error
            wp_send_json_error(array('message' => 'Invalid station_id'));
        }
    } else {
        if (!$station_id) {
            error_log('Missing station_id'); // Log error
            wp_send_json_error(array('message' => 'Missing station_id'));
        } elseif (!$passkey) {
            error_log('Missing passkey'); // Log error
            wp_send_json_error(array('message' => 'Missing passkey'));
        }
    }

    wp_die(); // Required to terminate AJAX request properly
}

// Handle AJAX request for posting weather data
add_action('wp_ajax_post_weather_data', 'post_weather_data');
add_action('wp_ajax_nopriv_post_weather_data', 'post_weather_data');
?>