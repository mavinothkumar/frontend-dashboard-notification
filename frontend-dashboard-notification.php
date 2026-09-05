<?php
/**
 * Plugin Name: Frontend Dashboard Notification
 * Plugin URI: https://buffercode.com/plugin/frontend-dashboard-notification
 * Description: Frontend Dashboard Notification is an add-on for Frontend Dashboard WordPress plugin which allows user to show notification in Frontend Dashboard page.
 * Version: 3.0.0
 * Author: vinoth06
 * Author URI: https://buffercode.com/
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: frontend-dashboard-notification
 * Domain Path:/languages
 *
 * @package frontened-dashboard-notification
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
	define( 'BC_FED_NTF_PLUGIN_VERSION', '3.0.0' );
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
} else {
	function fed_global_fed_notification() {
		?>
		<div class="notice notice-warning">
			<p>
				<b>
					<?php
					esc_attr_e( 'Please install <a href="https://buffercode.com/plugin/frontend-dashboard">Frontend Dashboard</a> to use this plugin [Frontend Dashboard Pages]','frontend-dashboard-pages' );
					?>
				</b>
			</p>
		</div>
		<?php
	}

	add_action( 'admin_notices', 'fed_global_fed_notification' );
	?>
	<?php
}
