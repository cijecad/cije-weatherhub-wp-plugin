<?php
/**
 * Plugin Name: CIJE Weather Hub
 * Plugin URI: https://cijeweatherhub.site/
 * Description: A plugin to manage weather data and display weather maps.
 * Version: 1.0.0
 * Author: Christopher Auger-Dominguez
 * Author URI: https://thecije.org/
 * License: GPL2
 */

// Plugin Root File
define( 'WEATHERHUB_PLUGIN_FILE', __FILE__ );

// Plugin base
define( 'WEATHERHUB_PLUGIN_BASE', plugin_basename( WEATHERHUB_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'WEATHERHUB_PLUGIN_DIR', plugin_dir_path( WEATHERHUB_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'WEATHERHUB_PLUGIN_URL', plugin_dir_url( WEATHERHUB_PLUGIN_FILE ) );

/**
 * Log a message to confirm the main plugin file is being loaded
 */
error_log('CIJE Weather Hub plugin file loaded');

/**
 * Load the main class for the core functionality
 */
require_once WEATHERHUB_PLUGIN_DIR . 'core/class-cije-weather-hub.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @since   1.0.0
 * @return  object|Cije_Weather_Hub
 */
function WEATHERHUB() {
    return Cije_Weather_Hub::instance();
}

WEATHERHUB();