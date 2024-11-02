<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

// Ensure this script is only accessible via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return; // Exit if not accessed via POST
}

// Include the WordPress environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Log a message to confirm the script is being called
error_log('post-weather-data.php script called');

// Retrieve POST data
$station_id = $_POST['station_id'] ?? null;
$passkey = $_POST['passkey'] ?? null;
$temperature = $_POST['temperature'] ?? null;
$humidity = $_POST['humidity'] ?? null;
$pressure = $_POST['pressure'] ?? null;
$wind_speed = $_POST['wind_speed'] ?? null;
$rain_inches = $_POST['rain_inches'] ?? null;

// Log received data for debugging
error_log('Received data: ' . print_r($_POST, true));

// Validate required fields
if ($station_id === null || $passkey === null || $temperature === null || $humidity === null || $pressure === null || $wind_speed === null || $rain_inches === null) {
    http_response_code(400);
    wp_send_json_error('Bad Request: Missing required fields');
    exit;
}

// Check if the station ID and passkey match
global $wpdb;
$stations_table = $wpdb->prefix . 'weather_stations';
$station = $wpdb->get_row($wpdb->prepare("SELECT * FROM $stations_table WHERE station_id = %d AND passkey = %s", $station_id, $passkey));

if (!$station) {
    error_log('Invalid station ID or passkey');
    http_response_code(403);
    wp_send_json_error('Invalid station ID or passkey');
    exit;
}

// Validate data ranges (allow zero values)
$errors = [];
if ($temperature < -50 || $temperature > 150) {
    $errors[] = 'Temperature out of range (-50 to 150 °F)';
}
if ($humidity < 0 || $humidity > 100) {
    $errors[] = 'Humidity out of range (0 to 100 %)';
}
if ($pressure != 0 && ($pressure < 800 || $pressure > 1100)) {
    $errors[] = 'Pressure out of range (0 or 800 to 1100 hPa)';
}
if ($wind_speed < 0 || $wind_speed > 200) {
    $errors[] = 'Wind speed out of range (0 to 200 mph)';
}
if ($rain_inches < 0 || $rain_inches > 100) {
    $errors[] = 'Precipitation out of range (0 to 100 inches)';
}

if (!empty($errors)) {
    $error_message = 'Data out of range: ' . implode(', ', $errors);
    error_log($error_message);
    http_response_code(400);
    wp_send_json_error($error_message);
    exit;
}

// Check the last data timestamp
$data_table = $wpdb->prefix . 'weather_data';
$last_entry = $wpdb->get_row($wpdb->prepare("SELECT date_time FROM $data_table WHERE station_id = %d ORDER BY date_time DESC LIMIT 1", $station_id));

if ($last_entry) {
    $last_time = strtotime($last_entry->date_time);
    $current_time = time();
    if (($current_time - $last_time) < 3600) { // 3600 seconds = 1 hour
        error_log('Post too soon');
        http_response_code(429);
        wp_send_json_error('Post too soon. Please wait an hour.');
        exit;
    }
}

// Process the data (e.g., save to database)
$table_name = $wpdb->prefix . 'weather_data';

// Log the table name for debugging
error_log('Table name: ' . $table_name);

// Insert data into the database
$inserted = $wpdb->insert(
    $table_name,
    array(
        'station_id' => $station_id,
        'temperature' => $temperature,
        'humidity' => $humidity,
        'pressure' => $pressure,
        'wind_speed' => $wind_speed,
        'precipitation' => $rain_inches
    ),
    array(
        '%d', '%f', '%f', '%f', '%f', '%f'
    )
);

// Log the SQL query and any errors for debugging
error_log('SQL Query: ' . $wpdb->last_query);
error_log('SQL Error: ' . $wpdb->last_error);

if ($inserted) {
    error_log('Data inserted successfully');
    http_response_code(200);
    wp_send_json_success('Data received successfully');
} else {
    error_log('Failed to insert data: ' . $wpdb->last_error);
    http_response_code(500);
    wp_send_json_error('Failed to insert data');
}
?>