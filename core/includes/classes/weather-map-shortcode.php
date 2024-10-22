<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

function weather_map_shortcode($atts) {
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

    // Fetch weather stations from the database
    $table_name = $wpdb->prefix . 'weather_stations';
    $stations = $wpdb->get_results("SELECT station_name, school, latitude, longitude FROM $table_name");

    wp_send_json_success(array('stations' => $stations));
}

add_action('wp_ajax_fetch_weather_stations', 'fetch_weather_stations');
add_action('wp_ajax_nopriv_fetch_weather_stations', 'fetch_weather_stations');
?>