<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Cije_Weather_Hub_Wp_Plugin_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package		WEATHERHUB
 * @subpackage	Classes/Cije_Weather_Hub_Wp_Plugin_Run
 * @author		Christopher Auger-Dominguez
 * @since		1.0.0
 */
class Cije_Weather_Hub_Wp_Plugin_Run{

	/**
	 * Our Cije_Weather_Hub_Wp_Plugin_Run constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct(){
		$this->add_hooks();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.0.0
	 * @return	void
	 */
	private function add_hooks(){
	
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_scripts_and_styles' ), 20 );
	
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOK CALLBACKS
	 * ###
	 * ######################
	 */

	/**
	 * Enqueue the backend related scripts and styles for this plugin.
	 * All of the added scripts andstyles will be available on every page within the backend.
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function enqueue_backend_scripts_and_styles() {
		wp_enqueue_script( 'weatherhub-backend-scripts', WEATHERHUB_PLUGIN_URL . 'core/includes/assets/js/backend-scripts.js', array(), WEATHERHUB_VERSION, false );
		wp_localize_script( 'weatherhub-backend-scripts', 'weatherhub', array(
			'plugin_name'   	=> __( WEATHERHUB_NAME, 'cije-weather-hub-wp-plugin' ),
		));
	}

}
