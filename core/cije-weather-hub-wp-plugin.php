<?php
/*
Plugin Name: The CIJE Weather Hub Wordpress Plugin
Description: A plugin to register and display weather station data from schools.
Version: 1.0
Author: Your Name
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

if (!class_exists('Cije_Weather_Hub_Wp_Plugin')) :

    /**
     * Main Cije_Weather_Hub_Wp_Plugin Class.
     *
     * @package     WEATHERHUB
     * @subpackage  Classes/Cije_Weather_Hub_Wp_Plugin
     * @since       1.0.0
     */
    final class Cije_Weather_Hub_Wp_Plugin {

        private static $instance;

        public $helpers;
        public $settings;

        public function __clone() {
            _doing_it_wrong(__FUNCTION__, __('You are not allowed to clone this class.', 'cije-weather-hub-wp-plugin'), '1.0.0');
        }

        public function __wakeup() {
            _doing_it_wrong(__FUNCTION__, __('You are not allowed to unserialize this class.', 'cije-weather-hub-wp-plugin'), '1.0.0');
        }

        public static function instance() {
            if (!isset(self::$instance) && !(self::$instance instanceof Cije_Weather_Hub_Wp_Plugin)) {
                self::$instance = new Cije_Weather_Hub_Wp_Plugin;
                self::$instance->base_hooks();
                self::$instance->includes();
                self::$instance->helpers = new Cije_Weather_Hub_Wp_Plugin_Helpers();
                self::$instance->settings = new Cije_Weather_Hub_Wp_Plugin_Settings();

                // Fire the plugin logic
                new Cije_Weather_Hub_Wp_Plugin_Run();

                // Fire a custom action to allow dependencies after the successful plugin setup
                do_action('WEATHERHUB/plugin_loaded');
            }

            return self::$instance;
        }

        private function includes() {
            require_once plugin_dir_path(__FILE__) . 'core/includes/classes/class-cije-weather-hub-wp-plugin-helpers.php';
            require_once plugin_dir_path(__FILE__) . 'core/includes/classes/class-cije-weather-hub-wp-plugin-settings.php';
            require_once plugin_dir_path(__FILE__) . 'core/includes/classes/class-cije-weather-hub-wp-plugin-run.php';

            // Include other required files
            include_once(plugin_dir_path(__FILE__) . 'core/weather-hub.php');
            include_once(plugin_dir_path(__FILE__) . 'core/post-weather-data.php');
            include_once(plugin_dir_path(__FILE__) . 'core/fetch-latest-weather-data.php');
            include_once(plugin_dir_path(__FILE__) . 'core/weather_graph_shortcode.php');
            include_once(plugin_dir_path(__FILE__) . 'core/register-station.php');
        }

        private function base_hooks() {
            add_action('plugins_loaded', array(self::$instance, 'load_textdomain'));
            add_action('wp_enqueue_scripts', array(self::$instance, 'enqueue_weather_hub_scripts'));
            register_activation_hook(__FILE__, array(self::$instance, 'weather_hub_create_tables'));
        }

        public function load_textdomain() {
            load_plugin_textdomain('cije-weather-hub-wp-plugin', FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        public function enqueue_weather_hub_scripts() {
            wp_enqueue_script('weather-hub-js', plugins_url('/core/weather-hub.js', __FILE__), array('jquery'), null, true);
            wp_localize_script('weather-hub-js', 'weatherHubSettings', array('ajax_url' => admin_url('admin-ajax.php')));
        }

        public function weather_hub_create_tables() {
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
    }

endif;

// Initialize the plugin
Cije_Weather_Hub_Wp_Plugin::instance();
?>