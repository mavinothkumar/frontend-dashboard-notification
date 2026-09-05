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
			add_action( 'wp_ajax_fed_notification_close_action', array( $this, 'notification_close_action' ) );
			add_action( 'wp_ajax_nopriv_fed_notification_close_action', 'fed_block_the_action' );
		}

		/**
		 * Fire Add Hooks inside the Dashboard.
		 */
		public function dashboard() {
			if ( fed_is_dashboard() ) {
				$user_notification_settings = get_user_meta( get_current_user_id(), 'fed_notification_user_settings',
					true );
				$user_notification_settings = ! empty( $user_notification_settings ) ? $user_notification_settings : array();
				$notification_object        = new FED_NTF_Notification_Controller();
				$notifications              = $notification_object->get();
				foreach ( $notifications as $notification ) {
					$notification_meta = get_post_meta( $notification->ID, 'fed_ntf_notification', true );
					if (
						isset( $notification_meta['locations'], $notification_meta['menus'], $notification_meta['user_roles'] ) &&
						count( $notification_meta['locations'] ) &&
						count( $notification_meta['user_roles'] ) &&
						count( $notification_meta['menus'] )
					) {
						if ( fed_is_current_user_role( $notification_meta['user_roles'], false ) ) {
							foreach ( $notification_meta['menus'] as $menu ) {
								foreach ( $notification_meta['locations'] as $location ) {
									$action_name      = 'fed_dashboard_' . $location . '_' . $menu;
									$user_action_name = $action_name . '_' . $notification->ID;
									if ( ! in_array(
										$user_action_name,
										$user_notification_settings,
										true
									)
									) {
										add_action( $action_name,
											function () use (
												$notification,
												$notification_meta,
												$action_name,
												$user_action_name
											) {
												?>
												<div class="fed_notification_container">
													<?php
													if ( isset( $notification_meta['close_button'] ) && 'Enable' === $notification_meta['close_button'] ) {
														$btn_action = isset( $notification_meta['close_action'] ) &&
														              'close_permanently' === $notification_meta['close_action'] ?
															esc_url( add_query_arg( array(
																'button_action' => $user_action_name,
																'fed_nonce'     => wp_create_nonce( 'fed_nonce' ),
															),
																fed_get_ajax_form_action( 'fed_notification_close_action' )
															) ) : false;
														?>
														<div class="fed_notification_close_button"
																data-url="<?php
																//phpcs:ignore
																echo $btn_action ? esc_url( $btn_action ) : '#';
																?>">
															<div class="fed_notification_close_x">
																<i class="fa fa-times-circle"></i>
															</div>
														</div>
														<?php
													}
													echo wp_kses_post( $notification->post_content );
													?>
												</div>
												<?php
											}
										);
									}
								}
							}
						}
					}
				}
			}
		}

		public function notification_close_action() {
			$get_payload = filter_input_array( INPUT_GET, FILTER_SANITIZE_STRING );

			fed_verify_nonce( $get_payload );

			if ( isset( $get_payload['button_action'] ) && ! empty( $get_payload['button_action'] ) ) {
				$user_meta = get_user_meta( get_current_user_id(), 'fed_notification_user_settings', true );
				$user_meta = is_array( $user_meta ) ? $user_meta : array();
				if ( ! in_array( $get_payload['button_action'], $user_meta ) ) {
					array_push( $user_meta, $get_payload['button_action'] );
					update_user_meta( get_current_user_id(), 'fed_notification_user_settings', $user_meta );
				}
				wp_send_json_success();
			}
		}
	}

	new FED_NTF_Dashboard_Notification();
}
