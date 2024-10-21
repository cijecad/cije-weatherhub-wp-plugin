<?php
/*
Plugin Name: The CIJE Weather Hub Wordpress Plugin
Description: A plugin to register and display weather station data from schools.
Version: 1.0
Author: Your Name
*/

// Include required files
include_once(plugin_dir_path(__FILE__) . 'weather-hub.php');
include_once(plugin_dir_path(__FILE__) . 'post-weather-data.php');
include_once(plugin_dir_path(__FILE__) . 'fetch-latest-weather-data.php');
include_once(plugin_dir_path(__FILE__) . 'weather_graph_shortcode.php');
include_once(plugin_dir_path(__FILE__) . 'register-station.php');


// Enqueue scripts and styles
function enqueue_weather_hub_scripts() {
    wp_enqueue_script('weather-hub-js', plugins_url('/weather-hub.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('weather-hub-js', 'weatherHubSettings', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'enqueue_weather_hub_scripts');

// Create the database tables on activation
register_activation_hook(__FILE__, 'weather_hub_create_tables');

function weather_hub_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table to register weather stations
    $stations_table = $wpdb->prefix . 'weather_stations';
    $stations_sql = "CREATE TABLE $stations_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        station_name varchar(100) NOT NULL,
        station_id varchar(50) NOT NULL,
        latitude float(10, 6) NOT NULL,
        longitude float(10, 6) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Table to record weather data
    $data_table = $wpdb->prefix . 'weather_data';
    $data_sql = "CREATE TABLE $data_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        station_id varchar(50) NOT NULL,
        temperature float(5, 2),
        humidity float(5, 2),
        pressure float(7, 2),
        wind_speed float(5, 2),
        datetime datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($stations_sql);
    dbDelta($data_sql);
}
?>