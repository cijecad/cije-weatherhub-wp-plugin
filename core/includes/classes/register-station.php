<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

function register_station_shortcode($atts) {
    // Generate a simple math CAPTCHA
    $num1 = rand(1, 9);
    $num2 = rand(1, 9);
    $captcha_question = "$num1 + $num2 = ?";
    $captcha_answer = $num1 + $num2;

    // Enqueue the JavaScript file
    wp_enqueue_script('register-station-js', plugins_url('core/includes/assets/js/register-station.js', dirname(__FILE__)), array('jquery'), null, true);

    // Localize script to pass AJAX URL and other settings
    wp_localize_script('register-station-js', 'registerStationSettings', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));

    // Output the HTML for the register station form
    ob_start();
    ?>
    <form id="register-station-form" style="display: flex; flex-direction: column; max-width: 400px;">
        <label for="station-name">Station Name: <span style="color: red;">*</span></label>
        <input type="text" id="station-name" name="station_name" required>

        <label for="school">School: <span style="color: red;">*</span></label>
        <input type="text" id="school" name="school" required>

        <label for="zip-code">Zip Code: <span style="color: red;">*</span></label>
        <input type="text" id="zip-code" name="zip_code" required>

        <label for="latitude">Latitude:</label>
        <input type="text" id="latitude" name="latitude">

        <label for="longitude">Longitude:</label>
        <input type="text" id="longitude" name="longitude">

        <label for="email">Email: <span style="color: red;">*</span></label>
        <input type="email" id="email" name="email" required>

        <label for="captcha">What is <?php echo $captcha_question; ?> <span style="color: red;">*</span></label>
        <input type="text" id="captcha" name="captcha" required>
        <input type="hidden" id="captcha_answer" name="captcha_answer" value="<?php echo $captcha_answer; ?>">

        <button type="submit">Register Station</button>
    </form>
    <div id="registration-result"></div>
    <p style="color: red;">* Required field</p>
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
    $captcha = sanitize_text_field($_POST['captcha']);
    $captcha_answer = sanitize_text_field($_POST['captcha_answer']);

    // Debugging statements
    error_log('Station Name: ' . $station_name);
    error_log('School: ' . $school);
    error_log('Zip Code: ' . $zip_code);
    error_log('Latitude: ' . $latitude);
    error_log('Longitude: ' . $longitude);
    error_log('Email: ' . $email);
    error_log('Captcha: ' . $captcha);
    error_log('Captcha Answer: ' . $captcha_answer);

    // Verify CAPTCHA
    if ($captcha != $captcha_answer) {
        wp_send_json_error(array('message' => 'CAPTCHA verification failed.'));
    }

    // Check if station_name is unique
    $table_name = $wpdb->prefix . 'weather_stations';
    $existing_station = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE station_name = %s", $station_name));
    if ($existing_station > 0) {
        wp_send_json_error(array('message' => 'Station name already exists.'));
    }

    // Generate a random 6-character alphanumeric passkey
    $passkey = wp_generate_password(6, false);

    // Insert the new station into the database
    $inserted = $wpdb->insert($table_name, array(
        'station_name' => $station_name,
        'school' => $school,
        'zip_code' => $zip_code,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'email' => $email,
        'passkey' => $passkey
    ));

    if ($inserted === false) {
        wp_send_json_error(array('message' => 'Failed to register station.'));
    }

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