<?php
/**
 * Dashboard Notification.
 *
 * @package frontend-dashboard-notification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FED_NTF_Dashboard_Notification' ) ) {
	/**
	 * Class FED_NTF_Dashboard_Notification
	 */
	class FED_NTF_Dashboard_Notification {
		/**
		 * FED_NTF_Dashboard_Notification constructor.
		 */
		public function __construct() {
			add_action( 'template_redirect', array( $this, 'dashboard' ) );
		}

		public function dashboard() {
			if ( fed_is_dashboard() ) {
				$notification_object = new FED_NTF_Notification_Controller();
				$notifications       = $notification_object->get();
				foreach ( $notifications as $notification ) {
					add_action( 'fed_dashboard_content_outside_top', function () {
						echo 'hl';
					} );
				}
			}
		}
	}

	new FED_NTF_Dashboard_Notification();
}