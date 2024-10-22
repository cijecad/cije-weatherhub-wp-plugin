<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

function register_station_shortcode($atts) {
    // Output the HTML for the register station form
    ob_start();
    ?>
    <form id="register-station-form">
        <label for="station-name">Station Name:</label>
        <input type="text" id="station-name" name="station_name" required>

        <label for="school">School:</label>
        <input type="text" id="school" name="school" required>

        <label for="zip-code">Zip Code:</label>
        <input type="text" id="zip-code" name="zip_code" required>

        <label for="latitude">Latitude:</label>
        <input type="text" id="latitude" name="latitude" required>

        <label for="longitude">Longitude:</label>
        <input type="text" id="longitude" name="longitude" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <button type="submit">Register Station</button>
    </form>
    <div id="registration-result"></div>
    <script>
        jQuery(document).ready(function($) {
            $('#register-station-form').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: formData + '&action=register_station',
                    success: function(response) {
                        $('#registration-result').html(response.data.message);
                    }
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

function handle_register_station() {
    global $wpdb;

    // Sanitize and validate input
    $station_name = sanitize_text_field($_POST['station_name']);
    $school = sanitize_text_field($_POST['school']);
    $zip_code = sanitize_text_field($_POST['zip_code']);
    $latitude = sanitize_text_field($_POST['latitude']);
    $longitude = sanitize_text_field($_POST['longitude']);
    $email = sanitize_email($_POST['email']);

    // Check if station_name is unique
    $table_name = $wpdb->prefix . 'weather_stations';
    $existing_station = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE station_name = %s", $station_name));
    if ($existing_station > 0) {
        wp_send_json_error(array('message' => 'Station name already exists.'));
    }

    // Generate a random 6-character alphanumeric passkey
    $passkey = wp_generate_password(6, false);

    // Insert the new station into the database
    $wpdb->insert($table_name, array(
        'station_name' => $station_name,
        'school' => $school,
        'zip_code' => $zip_code,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'email' => $email,
        'passkey' => $passkey
    ));

    // Get the station_id of the newly inserted station
    $station_id = $wpdb->insert_id;

    // Send an email to the user with their station_id and passkey
    $subject = 'Your Weather Station Registration';
    $message = "Thank you for registering your weather station.\n\n";
    $message .= "Station ID: $station_id\n";
    $message .= "Passkey: $passkey\n";
    wp_mail($email, $subject, $message);

    wp_send_json_success(array('message' => 'Registration successful. Please check your email for your station ID and passkey.'));
}

add_action('wp_ajax_register_station', 'handle_register_station');
add_action('wp_ajax_nopriv_register_station', 'handle_register_station');