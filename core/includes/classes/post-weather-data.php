<?php
// Include the WordPress environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Log a message to confirm the script is being called
error_log('post-weather-data.php script called');

// Ensure this script is only accessible via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

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
if (!$station_id || !$passkey || !$temperature || !$humidity || !$pressure || !$wind_speed || !$rain_inches) {
    http_response_code(400);
    echo 'Bad Request: Missing required fields';
    exit;
}

// Check if the station ID and passkey match
global $wpdb;
$stations_table = $wpdb->prefix . 'weather_stations';
$station = $wpdb->get_row($wpdb->prepare("SELECT * FROM $stations_table WHERE station_id = %d AND passkey = %s", $station_id, $passkey));

if (!$station) {
    error_log('Invalid station ID or passkey');
    http_response_code(403);
    echo 'Invalid station ID or passkey';
    exit;
}

// Validate data ranges
if ($temperature < -50 || $temperature > 150 || $humidity < 0 || $humidity > 100 || $pressure < 800 || $pressure > 1100 || $wind_speed < 0 || $wind_speed > 200 || $rain_inches < 0 || $rain_inches > 100) {
    error_log('Data out of range');
    http_response_code(400);
    echo 'Data out of range';
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
        echo 'Post too soon. Please wait an hour.';
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

if ($inserted) {
    error_log('Data inserted successfully');
    echo 'Data received successfully';
} else {
    error_log('Failed to insert data: ' . $wpdb->last_error);
    http_response_code(500);
    echo 'Failed to insert data';
}
?>