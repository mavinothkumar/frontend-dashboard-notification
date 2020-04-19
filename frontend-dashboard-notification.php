<?php
/**
 * Plugin Name: Frontend Dashboard Notification
 * Plugin URI: https://buffercode.com/plugin/frontend-dashboard-notification
 * Description: Frontend dashboard makes you flexible way to customize the user dashboard on frontend rather than WordPress wp-admin dashboard.
 * Version: 1.0
 * Author: vinoth06
 * Author URI: https://buffercode.com/
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: frontend-dashboard-notification
 * Domain Path: /languages
 *
 * @package frontened-dashboard-notification
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Version Number
 */
define( 'BC_FED_NTF_PLUGIN_VERSION', '1.0' );
define( 'BC_FED_NTF_PLUGIN_VERSION_TYPE', 'PRO' );

/**
 * App Name
 */
define( 'BC_FED_NTF_APP_NAME', 'Frontend Dashboard Notification' );

/**
 * Root Path
 */
define( 'BC_FED_NTF_PLUGIN', __FILE__ );
/**
 * Plugin Base Name
 */
define( 'BC_FED_NTF_PLUGIN_BASENAME', plugin_basename( BC_FED_NTF_PLUGIN ) );
/**
 * Plugin Name
 */
define( 'BC_FED_NTF_PLUGIN_NAME', trim( dirname( BC_FED_NTF_PLUGIN_BASENAME ), '/' ) );
/**
 * Plugin Directory
 */
define( 'BC_FED_NTF_PLUGIN_DIR', untrailingslashit( dirname( BC_FED_NTF_PLUGIN ) ) );

require_once BC_FED_NTF_PLUGIN_DIR . '/fed-ntf-autoload.php';
