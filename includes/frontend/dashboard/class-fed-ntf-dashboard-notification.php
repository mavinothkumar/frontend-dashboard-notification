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
					$notification_meta = get_post_meta( $notification->ID, 'fed_ntf_notification', true );
					if (
						isset( $notification_meta['locations'], $notification_meta['menus'] ) &&
						count( $notification_meta['locations'] ) &&
						count( $notification_meta['menus'] )
					) {
						foreach ( $notification_meta['menus'] as $menu ) {
							foreach ( $notification_meta['locations'] as $location ) {
								add_action( 'fed_dashboard_' . $location . '_' . $menu,
									function () use ( $notification ) {
										echo wp_kses_post( $notification->post_content );
									}
								);
							}
						}
					}
				}
			}
		}
	}

	new FED_NTF_Dashboard_Notification();
}