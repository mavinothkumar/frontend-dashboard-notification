<?php
/**
 * Autoload Files for Frontend Dashboard Notification.
 *
 * @package Frontend Dashboard Notification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$files = array(
	'/includes/function.php',
	'/includes/admin/custom-post/class-fed-ntf-custom-post.php',
	'/includes/admin/menu/class-fed-ntf-admin-settings.php',
	'/includes/frontend/controller/class-fed-ntf-notification-controller.php',
	'/includes/frontend/dashboard/class-fed-ntf-dashboard-notification.php',
	'/assets/enqueue.php',
);

foreach ( $files as $file ) {
	if ( file_exists( BC_FED_NTF_PLUGIN_DIR . $file ) ) {
		require_once BC_FED_NTF_PLUGIN_DIR . $file;
	}
}
