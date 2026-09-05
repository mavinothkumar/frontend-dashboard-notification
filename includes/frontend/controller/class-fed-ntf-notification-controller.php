<?php
/**
 * Notification Controller.
 *
 * @package frontend-dashboard-notification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FED_NTF_Notification_Controller' ) ) {
	/**
	 * Class FED_NTF_Notification_Controller
	 */
	class FED_NTF_Notification_Controller {
		/**
		 * Get Notifications.
		 *
		 * @param  string $status  Status.
		 *
		 * @return int[]|\WP_Post[]
		 */
		public function get( $status = 'Enable' ) {
			$options = array(
				'numberposts' => - 1,
				'post_type'   => 'fed-notification',
				'post_status' => 'publish',
				// phpcs:ignore
				'meta_query'  => array(
					array(
						'key'   => 'fed_ntf_notification_status',
						'value' => $status,
					),
				),
			);

			return get_posts( $options );
		}
	}
}
