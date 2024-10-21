<?php
// Register the weather station and generate shortcode
function register_weather_station_shortcode() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_weather_station'])) {
        $recaptcha_secret = '6LfRygYUAAAAAKvaYgNjWj40tADyfABZgfAG9UDl'; // Add your reCAPTCHA secret key here
        $response = $_POST['g-recaptcha-response'];
        $remoteip = $_SERVER['REMOTE_ADDR'];

        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_response = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $response . '&remoteip=' . $remoteip);
        $recaptcha_response = json_decode($recaptcha_response);

        if ($recaptcha_response->success) {
            if (isset($_POST['station_name'], $_POST['school'], $_POST['teacher'], $_POST['email'], $_POST['zip_code'])) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'weather_stations';

                $station_name = sanitize_text_field($_POST['station_name']);
                $school = sanitize_text_field($_POST['school']);
                $teacher = sanitize_text_field($_POST['teacher']);
                $email = sanitize_email($_POST['email']);
                $zip_code = sanitize_text_field($_POST['zip_code']);
                $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
                $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;

                // Check if the station name already exists
                $existing_station = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE station_name = %s", $station_name));
                if ($existing_station > 0) {
                    echo '<div class="error">Error: Station name already exists.</div>';
                } else {
                    // Fetch latitude and longitude using Zip Code if not provided
                    if (is_null($latitude) || is_null($longitude)) {
                        $location_data = file_get_contents('https://api.bigdatacloud.net/data/reverse-geocode-client?postalCode=' . $zip_code . '&countryCode=US&localityLanguage=en');
                        $location_data = json_decode($location_data);
                        if ($location_data && isset($location_data->latitude, $location_data->longitude)) {
                            $latitude = $location_data->latitude;
                            $longitude = $location_data->longitude;
                        }
                    }

                    // Insert the new weather station into the database
                    $wpdb->insert(
                        $table_name,
                        array(
                            'station_name' => $station_name,
                            'school'       => $school,
                            'teacher'      => $teacher,
                            'email'        => $email,
                            'zip_code'     => $zip_code,
                            'latitude'     => $latitude,
                            'longitude'    => $longitude,
                        ),
                        array(
                            '%s', '%s', '%s', '%s', '%s', '%f', '%f'
                        )
                    );

                    if ($wpdb->last_error) {
                        echo '<div class="error">Error: ' . esc_html($wpdb->last_error) . '</div>';
                    } else {
                        $station_id = $wpdb->insert_id;
                        // Send email with station ID
                        wp_mail($email, 'Weather Station Registered', 'Weather station registered successfully. Your Station ID is: ' . $station_id);
                        echo '<div class="success">Weather station registered successfully. An email has been sent with the station ID.</div>';
                    }
                }
            } else {
                echo '<div class="error">Error: Missing required fields.</div>';
            }
        } else {
            echo '<div class="error">Error: Please verify that you are not a robot.</div>';
        }
    }

    ob_start();
    ?>
    <form method="post">
        <label for="station_name">Station Name:</label>
        <input type="text" id="station_name" name="station_name" required><br>

        <label for="school">School:</label>
        <input type="text" id="school" name="school" required><br>

        <label for="teacher">Teacher:</label>
        <input type="text" id="teacher" name="teacher" required><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>

        <label for="zip_code">Zip Code:</label>
        <input type="text" id="zip_code" name="zip_code" required><br>

        <label for="latitude">Latitude (optional):</label>
        <input type="number" step="0.000001" id="latitude" name="latitude"><br>

        <label for="longitude">Longitude (optional):</label>
        <input type="number" step="0.000001" id="longitude" name="longitude"><br>

        <div class="g-recaptcha" data-sitekey="your-site-key"></div> <!-- Add your reCAPTCHA site key here -->
        <input type="submit" name="register_weather_station" value="Register Weather Station">
    </form>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <?php
    return ob_get_clean();
}
add_shortcode('register_weather_station', 'register_weather_station_shortcode');
?>