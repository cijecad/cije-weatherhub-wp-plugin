<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Cije_Weather_Hub_Wp_Plugin' ) ) :

	/**
	 * Main Cije_Weather_Hub_Wp_Plugin Class.
	 *
	 * @package		WEATHERHUB
	 * @subpackage	Classes/Cije_Weather_Hub_Wp_Plugin
	 * @since		1.0.0
	 * @author		Christopher Auger-Dominguez
	 */
	final class Cije_Weather_Hub_Wp_Plugin {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|Cije_Weather_Hub_Wp_Plugin
		 */
		private static $instance;

		/**
		 * WEATHERHUB helpers object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Cije_Weather_Hub_Wp_Plugin_Helpers
		 */
		public $helpers;

		/**
		 * WEATHERHUB settings object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Cije_Weather_Hub_Wp_Plugin_Settings
		 */
		public $settings;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'cije-weather-hub-wp-plugin' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'cije-weather-hub-wp-plugin' ), '1.0.0' );
		}

		/**
		 * Main Cije_Weather_Hub_Wp_Plugin Instance.
		 *
		 * Insures that only one instance of Cije_Weather_Hub_Wp_Plugin exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|Cije_Weather_Hub_Wp_Plugin	The one true Cije_Weather_Hub_Wp_Plugin
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Cije_Weather_Hub_Wp_Plugin ) ) {
				self::$instance					= new Cije_Weather_Hub_Wp_Plugin;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers		= new Cije_Weather_Hub_Wp_Plugin_Helpers();
				self::$instance->settings		= new Cije_Weather_Hub_Wp_Plugin_Settings();

				//Fire the plugin logic
				new Cije_Weather_Hub_Wp_Plugin_Run();

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'WEATHERHUB/plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function includes() {
			require_once WEATHERHUB_PLUGIN_DIR . 'core/includes/classes/class-cije-weather-hub-wp-plugin-helpers.php';
			require_once WEATHERHUB_PLUGIN_DIR . 'core/includes/classes/class-cije-weather-hub-wp-plugin-settings.php';

			require_once WEATHERHUB_PLUGIN_DIR . 'core/includes/classes/class-cije-weather-hub-wp-plugin-run.php';
		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function base_hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'cije-weather-hub-wp-plugin', FALSE, dirname( plugin_basename( WEATHERHUB_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.