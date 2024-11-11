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
    <div id="weather-map" style="width: 100%; height: 500px; background-color: lightgray;">
        <p>Weather map should appear here.</p>
    </div>
    <?php
    return ob_get_clean();
}
function fetch_weather_stations() {
    global $wpdb;

    // Log a message to confirm the function is being called
    error_log('fetch_weather_stations called');

    // Fetch weather stations and their most recent data from the database
    $results = $wpdb->get_results("
        SELECT 
            ws.station_id, 
            ws.station_name, 
            ws.latitude, 
            ws.longitude, 
            wd.temperature, 
            wd.humidity, 
            wd.pressure, 
            wd.wind_speed, 
            wd.date_time
        FROM {$wpdb->prefix}weather_stations ws
        LEFT JOIN (
            SELECT wd1.*
            FROM {$wpdb->prefix}weather_data wd1
            INNER JOIN (
                SELECT station_id, MAX(date_time) AS date_time
                FROM {$wpdb->prefix}weather_data
                GROUP BY station_id
            ) wd2 ON wd1.station_id = wd2.station_id AND wd1.date_time = wd2.date_time
        ) wd ON ws.station_id = wd.station_id
    ");

    // Log the results
    error_log('Weather stations fetched: ' . print_r($results, true));

    if ($results) {
        wp_send_json_success($results);
    } else {
        wp_send_json_error('Failed to fetch weather stations');
    }
}

add_action('wp_ajax_fetch_weather_stations', 'fetch_weather_stations');
add_action('wp_ajax_nopriv_fetch_weather_stations', 'fetch_weather_stations');
add_shortcode('weather-map', 'weather_map_shortcode');
?>