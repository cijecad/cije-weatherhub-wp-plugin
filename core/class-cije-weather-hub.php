<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Cije_Weather_Hub {

    /**
     * The single instance of the class.
     *
     * @var Cije_Weather_Hub
     */
    protected static $_instance = null;

    /**
     * Main Cije_Weather_Hub Instance.
     *
     * Ensures only one instance of Cije_Weather_Hub is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see WEATHERHUB()
     * @return Cije_Weather_Hub - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Cije_Weather_Hub Constructor.
     */
    public function __construct() {
        // Log a message to confirm the constructor is being called
        error_log('Cije_Weather_Hub constructor called');

        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes() {
        // Log a message to confirm the includes function is being called
        error_log('Cije_Weather_Hub includes called');

        include_once WEATHERHUB_PLUGIN_DIR . 'core/includes/classes/weather-map-shortcode.php';
        
        // Conditionally include post-weather-data.php only when accessed via POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['station_id']) && isset($_POST['passkey'])) {
            include_once WEATHERHUB_PLUGIN_DIR . 'core/includes/classes/post-weather-data.php';
        }

        include_once WEATHERHUB_PLUGIN_DIR . 'core/includes/classes/register-station.php';
    }

    /**
     * Hook into actions and filters.
     */
    private function init_hooks() {
        // Log a message to confirm the init_hooks function is being called
        error_log('Cije_Weather_Hub init_hooks called');

        add_action( 'init', array( $this, 'init' ), 0 );
    }

    /**
     * Init Cije Weather Hub when WordPress Initialises.
     */
    public function init() {
        // Log a message to confirm the init function is being called
        error_log('Cije_Weather_Hub init called');
    }
}