<?php
/**
 * CIJE Weather Hub WP Plugin
 *
 * @package       WEATHERHUB
 * @author        Christopher Auger-Dominguez
 * @license       gplv2
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   CIJE Weather Hub WP Plugin
 * Plugin URI:    https://cijeweatherhub.site/
 * Description:   A wordpress plugin to register and display DIY weather station data.
 * Version:       1.0.0
 * Author:        Christopher Auger-Dominguez
 * Author URI:    https://thecije.org/
 * Text Domain:   cije-weather-hub-wp-plugin
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with CIJE Weather Hub WP Plugin. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Plugin name
define( 'WEATHERHUB_NAME',			'CIJE Weather Hub WP Plugin' );

// Plugin version
define( 'WEATHERHUB_VERSION',		'1.0.0' );

// Plugin Root File
define( 'WEATHERHUB_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'WEATHERHUB_PLUGIN_BASE',	plugin_basename( WEATHERHUB_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'WEATHERHUB_PLUGIN_DIR',	plugin_dir_path( WEATHERHUB_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'WEATHERHUB_PLUGIN_URL',	plugin_dir_url( WEATHERHUB_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once WEATHERHUB_PLUGIN_DIR . 'core/class-cije-weather-hub-wp-plugin.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Christopher Auger-Dominguez
 * @since   1.0.0
 * @return  object|Cije_Weather_Hub_Wp_Plugin
 */
function WEATHERHUB() {
	return Cije_Weather_Hub_Wp_Plugin::instance();
}

WEATHERHUB();
