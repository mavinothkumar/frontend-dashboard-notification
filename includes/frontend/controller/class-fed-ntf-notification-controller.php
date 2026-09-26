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
		 * @param string $status Status ('Enable' or 'Disable' or null for all).
		 * @return WP_Post[]
		 */
		public function get( $status = 'Enable' ) {
			$args = array(
				'numberposts' => -1,
				'post_type'   => 'fed-notification',
				'post_status' => 'publish',
				'orderby'     => 'date',
				'order'       => 'DESC',
			);

			if ( ! empty( $status ) ) {
				$args['meta_query'] = array(
					array(
						'key'   => 'fed_ntf_notification_status',
						'value' => $status,
					),
				);
			}

			return get_posts( $args );
		}

		/**
		 * Get single notification by ID.
		 *
		 * @param int $id Notification Post ID.
		 * @return WP_Post|null
		 */
		public function get_by_id( $id ) {
			return get_post( (int) $id );
		}
	}
}
