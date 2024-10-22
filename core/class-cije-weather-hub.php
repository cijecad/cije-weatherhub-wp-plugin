<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

if (!class_exists('Cije_Weather_Hub')) :

    /**
     * Main Cije_Weather_Hub Class.
     *
     * @package     WEATHERHUB
     * @subpackage  Classes/Cije_Weather_Hub
     * @since       1.0.0
     */
    final class Cije_Weather_Hub {

        private static $instance;

        public $helpers;
        public $settings;

        public function __clone() {
            _doing_it_wrong(__FUNCTION__, __('You are not allowed to clone this class.', 'cije-weather-hub'), '1.0.0');
        }

        public function __wakeup() {
            _doing_it_wrong(__FUNCTION__, __('You are not allowed to unserialize this class.', 'cije-weather-hub'), '1.0.0');
        }

        public static function instance() {
            if (!isset(self::$instance) && !(self::$instance instanceof Cije_Weather_Hub)) {
                self::$instance = new Cije_Weather_Hub;
                self::$instance->base_hooks();
                self::$instance->includes();
                self::$instance->helpers = new Cije_Weather_Hub_Helpers();
                self::$instance->settings = new Cije_Weather_Hub_Settings();

                // Fire the plugin logic
                new Cije_Weather_Hub_Run();

                // Fire a custom action to allow dependencies after the successful plugin setup
                do_action('WEATHERHUB/plugin_loaded');
            }

            return self::$instance;
        }

        private function includes() {
            require_once plugin_dir_path(__FILE__) . 'includes/classes/class-cije-weather-hub-helpers.php';
            require_once plugin_dir_path(__FILE__) . 'includes/classes/class-cije-weather-hub-settings.php';
            require_once plugin_dir_path(__FILE__) . 'includes/classes/class-cije-weather-hub-run.php';

            // Include other required files
            include_once(plugin_dir_path(__FILE__) . 'includes/classes/weather-map-shortcode.php');
            include_once(plugin_dir_path(__FILE__) . 'includes/classes/post-weather-data.php');
            include_once(plugin_dir_path(__FILE__) . 'includes/classes/fetch-latest-weather-data.php');
            include_once(plugin_dir_path(__FILE__) . 'includes/classes/weather_data_graph_shortcode.php');
            include_once(plugin_dir_path(__FILE__) . 'includes/classes/register-station.php');
        }

        private function base_hooks() {
            add_action('plugins_loaded', array(self::$instance, 'load_textdomain'));
            add_action('wp_enqueue_scripts', array(self::$instance, 'enqueue_weather_hub_scripts'));
            register_activation_hook(WEATHERHUB_PLUGIN_FILE, array(self::$instance, 'weather_hub_create_tables'));

            // Register shortcodes and AJAX handlers
            add_action('init', array(self::$instance, 'register_shortcodes'));
            add_action('init', array(self::$instance, 'register_ajax_handlers'));
        }

        public function weather_hub_create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();

            // Create weather_stations table
            $table_name_stations = $wpdb->prefix . 'weather_stations';
            $sql_stations = "CREATE TABLE $table_name_stations (
                station_id mediumint(9) NOT NULL AUTO_INCREMENT,
                station_name tinytext NOT NULL,
                school tinytext NOT NULL,
                zip_code varchar(10) NOT NULL,
                latitude float(10, 6) NOT NULL,
                longitude float(10, 6) NOT NULL,
                email varchar(100) NOT NULL,
                passkey varchar(6) NOT NULL,
                PRIMARY KEY  (station_id)
            ) $charset_collate;";

            // Create weather_data table
            $table_name_data = $wpdb->prefix . 'weather_data';
            $sql_data = "CREATE TABLE $table_name_data (
                data_id mediumint(9) NOT NULL AUTO_INCREMENT,
                station_id mediumint(9) NOT NULL,
                date_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                temperature float(5, 2) NOT NULL,
                humidity float(5, 2) NOT NULL,
                pressure float(7, 2) NOT NULL,
                precipitation float(5, 2) NOT NULL,
                wind_speed float(5, 2) NOT NULL,
                PRIMARY KEY  (data_id),
                FOREIGN KEY (station_id) REFERENCES $table_name_stations(station_id) ON DELETE CASCADE
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql_stations);
            dbDelta($sql_data);
        }

        public function load_textdomain() {
            load_plugin_textdomain('cije-weather-hub', FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        public function enqueue_weather_hub_scripts() {
            wp_enqueue_script('weather-map-js', plugins_url('/includes/assets/js/weather-map.js', __FILE__), array('jquery', 'leaflet'), null, true);
        }

        // Register shortcodes
        public function register_shortcodes() {
            add_shortcode('weather_graph', 'weather_graph_shortcode');
            add_shortcode('register_station', 'register_station_shortcode');
            add_shortcode('weather_map', 'weather_map_shortcode');
        }

        // Register AJAX handlers
        public function register_ajax_handlers() {
            add_action('wp_ajax_fetch_weather_graph_data', 'fetch_weather_graph_data');
            add_action('wp_ajax_nopriv_fetch_weather_graph_data', 'fetch_weather_graph_data');
            add_action('wp_ajax_register_station', 'handle_register_station');
            add_action('wp_ajax_nopriv_register_station', 'handle_register_station');
            add_action('wp_ajax_fetch_weather_stations', 'fetch_weather_stations');
            add_action('wp_ajax_nopriv_fetch_weather_stations', 'fetch_weather_stations');
            add_action('wp_ajax_fetch_latest_weather_data', 'fetch_latest_weather_data');
            add_action('wp_ajax_nopriv_fetch_latest_weather_data', 'fetch_latest_weather_data');
        }
    }
endif;