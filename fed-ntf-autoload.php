<?php
/**
 * Include all files.
 *
 * @package Frontend Dashboard.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Admin Files
 */

$files = array(
	'/includes/admin/menu/class-fed-ntf-main-menu.php',
	'/includes/admin/menu/class-fed-ntf-menu-general.php',
	'/includes/admin/custom-post/class-fed-ntf-custom-post.php',
	'/includes/admin/custom-post/class-fed-ntf-custom-meta.php',
	'/includes/frontend/dashboard/class-fed-ntf-dashboard-notification.php',
	'/includes/frontend/controller/class-fed-ntf-notification-controller.php',
	'/assets/enqueue.php',
	'/includes/function.php',

);

/**
 * Disable Files.
 * array(
 * '/includes/frontend/controller/payment.php',
 * )
 */

foreach ( $files as $file ) {
	if ( file_exists( BC_FED_NTF_PLUGIN_DIR . $file ) ) {
		require_once BC_FED_NTF_PLUGIN_DIR . $file;
	}
}
