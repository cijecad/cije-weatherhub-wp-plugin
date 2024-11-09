<?php
/**
 * Plugin Name: CIJE Weather Hub
 * Plugin URI: https://cijeweatherhub.site/
 * Description: A plugin to display weather data and graphs for WordPress.
 * Version: 1.0.0
 * Author: Christopher Auger-Dominguez
 * Author URI: https://thecije.org
 * License: GPL2
 * Text Domain: cije-weather-hub
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Define plugin constants
define( 'WEATHERHUB_PLUGIN_FILE', __FILE__ );
define( 'WEATHERHUB_PLUGIN_BASE', plugin_basename( WEATHERHUB_PLUGIN_FILE ) );
define( 'WEATHERHUB_PLUGIN_DIR', plugin_dir_path( WEATHERHUB_PLUGIN_FILE ) );
define( 'WEATHERHUB_PLUGIN_URL', plugin_dir_url( WEATHERHUB_PLUGIN_FILE ) );

// Log a message to confirm the main plugin file is being loaded
error_log( 'CIJE Weather Hub plugin file loaded' );

// Register activation hook
register_activation_hook( __FILE__, 'cije_weather_hub_activate' );

// Activation function
function cije_weather_hub_activate() {
    error_log( 'cije_weather_hub_activate called' );
    cije_weather_hub_create_tables();
}

// Function to create database tables
function cije_weather_hub_create_tables() {
    global $wpdb;

    // Get the proper character set and collation
    $charset_collate = $wpdb->get_charset_collate();

    // Table names
    $weather_data_table = $wpdb->prefix . 'weather_data';
    $weather_stations_table = $wpdb->prefix . 'weather_stations';

    // SQL to create weather_stations table with additional fields
    $sql_stations = "CREATE TABLE $weather_stations_table (
        station_id int(11) NOT NULL AUTO_INCREMENT,
        station_name varchar(255) NOT NULL,
        school varchar(255) NOT NULL,
        zip_code varchar(10) NOT NULL,
        latitude decimal(10,7) NOT NULL,
        longitude decimal(10,7) NOT NULL,
        email varchar(255) NOT NULL,
        passkey varchar(50) NOT NULL,
        PRIMARY KEY (station_id)
    ) $charset_collate;";

    // SQL to create weather_data table
    $sql_weather_data = "CREATE TABLE $weather_data_table (
        id int(11) NOT NULL AUTO_INCREMENT,
        station_id int(11) NOT NULL,
        date_time datetime NOT NULL,
        temperature float DEFAULT NULL,
        humidity float DEFAULT NULL,
        pressure float DEFAULT NULL,
        wind_speed float DEFAULT NULL,
        precipitation float DEFAULT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (station_id) REFERENCES $weather_stations_table(station_id) ON DELETE CASCADE
    ) $charset_collate;";

    // Include the WordPress upgrade functions for dbDelta
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    // Run the SQL queries
    dbDelta( $sql_stations );
    dbDelta( $sql_weather_data );

    // Log messages to confirm table creation
    error_log( 'Weather stations table created or updated.' );
    error_log( 'Weather data table created or updated.' );
}

// Include the main class for the core functionality
require_once WEATHERHUB_PLUGIN_DIR . 'core/class-cije-weather-hub.php';

// Include admin menu functions
require_once WEATHERHUB_PLUGIN_DIR . 'core/includes/classes/admin-menu.php';

// The main function to load the only instance of our master class.
function WEATHERHUB() {
    return Cije_Weather_Hub::instance();
}

// Initialize the plugin
WEATHERHUB();