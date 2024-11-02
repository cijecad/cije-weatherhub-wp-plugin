<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

function weather_map_shortcode($atts) {
    // Log a message to confirm the shortcode is being called
    error_log('weather_map_shortcode called');

    // Enqueue the Leaflet CSS file
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');

    // Enqueue the Leaflet JavaScript file
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), null, true);

    // Enqueue the JavaScript file
    wp_enqueue_script('weather-map-js', plugins_url('../assets/js/weather-map.js', __FILE__), array('jquery', 'leaflet-js'), null, true);

    // Localize script to pass AJAX URL and other settings
    wp_localize_script('weather-map-js', 'weatherHubSettings', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));

    // Output the HTML for the weather map
    ob_start();
    ?>
    <div id="weather-map" style="width: 100%; height: 500px;"></div>
    <?php
    return ob_get_clean();
}

function fetch_weather_stations() {
    global $wpdb;

    // Log a message to confirm the function is being called
    error_log('fetch_weather_stations called');

    // Fetch weather stations and their last weather data entry from the database
    $table_stations = $wpdb->prefix . 'weather_stations';
    $table_data = $wpdb->prefix . 'weather_data';
    $stations = $wpdb->get_results("
        SELECT 
            ws.station_name, 
            ws.school, 
            ws.latitude, 
            ws.longitude, 
            wd.temperature, 
            wd.humidity, 
            wd.pressure, 
            wd.wind_speed, 
            wd.precipitation, 
            wd.date_time 
        FROM $table_stations ws
        LEFT JOIN $table_data wd ON ws.station_id = wd.station_id
        WHERE wd.date_time = (
            SELECT MAX(date_time) 
            FROM $table_data 
            WHERE station_id = ws.station_id
        )
    ");

    // Log the fetched data for debugging
    error_log('Fetched stations: ' . print_r($stations, true));

    if ($stations) {
        error_log('Weather stations fetched successfully');
        wp_send_json_success(array('stations' => $stations));
    } else {
        error_log('Failed to fetch weather stations');
        wp_send_json_error('Failed to fetch weather stations');
    }
}

add_action('wp_ajax_fetch_weather_stations', 'fetch_weather_stations');
add_action('wp_ajax_nopriv_fetch_weather_stations', 'fetch_weather_stations');
add_shortcode('weather-map', 'weather_map_shortcode');
?>