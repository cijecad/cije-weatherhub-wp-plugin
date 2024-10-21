<?php
// Register the weather station and generate shortcode
function register_weather_station_shortcode() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_weather_station'])) {
        if (isset($_POST['station_name'], $_POST['station_id'], $_POST['latitude'], $_POST['longitude'])) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'weather_stations';

            $station_name = sanitize_text_field($_POST['station_name']);
            $station_id = sanitize_text_field($_POST['station_id']);
            $latitude = floatval($_POST['latitude']);
            $longitude = floatval($_POST['longitude']);

            // Insert the new weather station into the database
            $wpdb->insert(
                $table_name,
                array(
                    'station_name' => $station_name,
                    'station_id'   => $station_id,
                    'latitude'     => $latitude,
                    'longitude'    => $longitude,
                ),
                array(
                    '%s', '%s', '%f', '%f'
                )
            );

            if ($wpdb->last_error) {
                echo '<div class="error">Error: ' . esc_html($wpdb->last_error) . '</div>';
            } else {
                echo '<div class="success">Weather station registered successfully.</div>';
            }
        } else {
            echo '<div class="error">Error: Missing required fields.</div>';
        }
    }

    ob_start();
    ?>
    <form method="post">
        <label for="station_name">Station Name:</label>
        <input type="text" id="station_name" name="station_name" required><br>

        <label for="station_id">Station ID:</label>
        <input type="text" id="station_id" name="station_id" required><br>

        <label for="latitude">Latitude:</label>
        <input type="number" step="0.000001" id="latitude" name="latitude" required><br>

        <label for="longitude">Longitude:</label>
        <input type="number" step="0.000001" id="longitude" name="longitude" required><br>

        <input type="submit" name="register_weather_station" value="Register Weather Station">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('register_weather_station', 'register_weather_station_shortcode');
?>