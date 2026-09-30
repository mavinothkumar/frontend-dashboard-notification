<?php
/**
 * Plugin Name: Frontend Dashboard Notification
 * Plugin URI: https://buffercode.com/plugin/frontend-dashboard-notification
 * Description: Frontend Dashboard Notification is an add-on for Frontend Dashboard WordPress plugin which allows you to show notifications in Frontend Dashboard pages.
 * Version: 3.0.2
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Requires Plugins: frontend-dashboard
 * Author: vinoth06
 * Author URI: https://buffercode.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: frontend-dashboard-notification
 * Domain Path: /languages
 *
 * @package frontend-dashboard-notification
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fed_check = get_option( 'fed_plugin_version' );

require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( $fed_check && is_plugin_active( 'frontend-dashboard/frontend-dashboard.php' ) ) {

	/**
	 * Version Number
	 */
	define( 'BC_FED_NTF_PLUGIN_VERSION', '3.0.2' );
	define( 'BC_FED_NTF_PLUGIN_VERSION_TYPE', 'FREE' );
	define( 'BC_FED_NTF_PLUGIN_SLUG', 'frontend-dashboard-notification' );

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
} else {
	/**
	 * Global Admin Notification for Frontend Dashboard Dependency
	 */
	function fed_global_fed_notification() {
		?>
		<div class="notice notice-warning">
			<p>
				<b>
					<?php
					printf(
						/* translators: 1: Plugin page URL, 2: Plugin Name */
						esc_html__( 'Please install and activate %1$s to use this plugin %2$s.', 'frontend-dashboard-notification' ),
						'<a href="https://buffercode.com/plugin/frontend-dashboard" target="_blank" rel="noopener noreferrer">Frontend Dashboard</a>',
						'[Frontend Dashboard Notification]'
					);
					?>
				</b>
			</p>
		</div>
		<?php
	}

	add_action( 'admin_notices', 'fed_global_fed_notification' );
}
