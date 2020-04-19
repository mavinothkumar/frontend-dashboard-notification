<?php
/**
 * Main Menu.
 *
 * @package Frontend Dashboard Notification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FED_NTF_Custom_Meta' ) ) {
	/**
	 * Class FED_NTF_Custom_Meta
	 */
	class FED_NTF_Custom_Meta {
		/**
		 * FED_NTF_Custom_Meta constructor.
		 */
		public function __construct() {
			add_action( 'save_post', array( $this, 'save_meta' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		}

		/**
		 * Save Meta Fields.
		 *
		 * @param  int $post_id  Post ID.
		 *
		 * @return mixed
		 */
		public function save_meta( $post_id ) {
			$request_payload = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

			if ( ! isset( $request_payload['fed_nonce'] ) ||
			     ! wp_verify_nonce( $request_payload['fed_nonce'], 'fed_nonce' )
			) {
				return $post_id;
			}
			// Check Autosave.
			if ( wp_is_post_autosave( $post_id ) ) {
				return $post_id;
			}

			// Check if not a revision.
			if ( wp_is_post_revision( $post_id ) ) {
				return $post_id;
			}

			// check permissions.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}

			if ( isset( $request_payload['fed_notification'] ) ) {
				update_post_meta( $post_id, 'fed_ntf_notification', $this->format_data( $request_payload ) );
			} else {
				delete_post_meta( $post_id, 'fed_ntf_notification' );
			}

			if ( isset( $request_payload['fed_notification_status'] ) ) {
				$status = isset( $request_payload['fed_notification_status'] ) ? esc_attr( $request_payload['fed_notification_status'] ) : 'Enable';
				update_post_meta( $post_id, 'fed_ntf_notification_status', $status );
			} else {
				delete_post_meta( $post_id, 'fed_ntf_notification_status' );
			}
		}

		/**
		 * Show Meta Box.
		 */
		public function show_meta_box() {
			global $post;
			$fed_ntf_notification        = get_post_meta( $post->ID, 'fed_ntf_notification', true );
			$fed_ntf_notification_status = get_post_meta( $post->ID, 'fed_ntf_notification_status', true );

			$locations = fed_ntf_notification_locations();

			$menu_items = fed_get_all_dashboard_display_menus();
			$menu_items = wp_list_pluck( $menu_items, 'menu', 'menu_slug' );

			$user_roles = fed_get_user_roles();
			?>

			<input type="hidden" name="common_author_post"
					value="<?php echo esc_attr( wp_create_nonce( 'fed_nonce' ) ); ?>">

			<div class="fed_ntf_notification_custom_meta">
				<div class="fed_ntf_menu">
					<div>
						<label>
							<?php
							esc_attr_e( 'Enable/Disable this Notification', 'frontend-dashboard-notification' );
							?>
						</label>
					</div>
					<div>
						<?php
						// phpcs:ignore
						echo fed_form_select(
							array(
								'input_value' => array(
									'Enable'  => 'Enable',
									'Disable' => 'Disable',
								),
								'input_meta'  => 'fed_notification_status',
								'user_value'  => fed_get_data(
									'fed_notification_status', $fed_ntf_notification_status,
									array()
								),
								'extra'       => 'style="width: 20%"',
							)
						);
						?>
					</div>
				</div>

				<div class="fed_ntf_location">
					<div>
						<label>
							<?php esc_attr_e( 'Select Locations', 'frontend-dashboard-notification' ); ?>
						</label>
					</div>
					<div>
						<?php
						// phpcs:ignore
						echo fed_form_select(
							array(
								'input_value' => $locations,
								'input_meta'  => 'fed_notification[locations]',
								'user_value'  => fed_get_data( 'locations', $fed_ntf_notification, array() ),
								'extra'       => 'style="width: 100%"',
								'extended'    => array( 'multiple' => 'Enable' ),
							)
						);
						?>
					</div>
				</div>

				<div class="fed_ntf_menu">
					<div>
						<label>
							<?php esc_attr_e( 'Select Menus', 'frontend-dashboard-notification' ); ?>
						</label>
					</div>
					<div>
						<?php
						// phpcs:ignore
						echo fed_form_select(
							array(
								'input_value' => $menu_items,
								'input_meta'  => 'fed_notification[menus]',
								'user_value'  => fed_get_data( 'menus', $fed_ntf_notification, array() ),
								'extra'       => 'style="width: 100%"',
								'extended'    => array( 'multiple' => 'Enable' ),
							)
						);
						?>
					</div>
				</div>

				<div class="fed_ntf_menu">
					<div>
						<label>
							<?php esc_attr_e( 'Select User Role', 'frontend-dashboard-notification' ); ?>
						</label>
					</div>
					<div>
						<?php
						// phpcs:ignore
						echo fed_form_select(
							array(
								'input_value' => $user_roles,
								'input_meta'  => 'fed_notification[user_roles]',
								'user_value'  => fed_get_data( 'user_roles', $fed_ntf_notification, array() ),
								'extra'       => 'style="width: 100%"',
								'extended'    => array( 'multiple' => 'Enable' ),
							)
						);
						?>
					</div>
				</div>

			</div>

			<?php
		}

		/**
		 * Add Meta Box.
		 */
		public function add_meta_boxes() {
			add_meta_box(
				'fed_ntf_notification',
				__( 'Settings', 'msd' ),
				array( $this, 'show_meta_box' ),
				array( 'fed-notification' ),
				'advanced',
				'high'
			);
		}

		/**
		 * Format Data.
		 *
		 * @param  array $request_payload  Request Payload.
		 *
		 * @return array
		 */
		private function format_data( $request_payload ) {

			return array(
				'menus'      => fed_get_data( 'fed_notification.menus', $request_payload, array() ),
				'locations'  => fed_get_data( 'fed_notification.locations', $request_payload, array() ),
				'user_roles' => fed_get_data( 'fed_notification.user_roles', $request_payload, array() ),
			);
		}
	}

	new FED_NTF_Custom_Meta();
}
