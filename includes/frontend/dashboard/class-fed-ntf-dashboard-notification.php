<?php
/**
 * Frontend Dashboard Notification Display Handler.
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
		}

		/**
		 * Attach Notification Render Hooks inside the Dashboard.
		 */
		public function dashboard() {
			if ( ! fed_is_dashboard() ) {
				return;
			}

			$current_user_id = get_current_user_id();
			$user_dismissed  = get_user_meta( $current_user_id, 'fed_notification_user_settings', true );
			if ( ! is_array( $user_dismissed ) ) {
				$user_dismissed = array();
			}

			$notification_controller = new FED_NTF_Notification_Controller();
			$notifications           = $notification_controller->get( 'Enable' );

			if ( empty( $notifications ) ) {
				return;
			}

			foreach ( $notifications as $notification ) {
				$meta = fed_ntf_get_notification_meta( $notification->ID );

				// Check if permanently dismissed by this user
				if ( in_array( (int) $notification->ID, $user_dismissed, true ) ||
				     in_array( (string) $notification->ID, $user_dismissed, true ) ||
				     in_array( 'fed_ntf_' . $notification->ID, $user_dismissed, true ) ) {
					continue;
				}

				// Check user role permission
				$roles = $meta['user_roles'];
				if ( ! in_array( 'all', $roles, true ) && ! empty( $roles ) ) {
					if ( ! fed_is_current_user_role( $roles, false ) ) {
						continue;
					}
				}

				// Attach to locations and menus
				$locations = $meta['locations'];
				$menus     = $meta['menus'];

				foreach ( $locations as $location ) {
					if ( in_array( 'all', $menus, true ) || empty( $menus ) ) {
						$action_name = 'fed_dashboard_' . sanitize_key( $location );
						add_action(
							$action_name,
							function () use ( $notification, $meta ) {
								$this->render_notification( $notification, $meta );
							}
						);
					} else {
						foreach ( $menus as $menu_slug ) {
							$action_name = 'fed_dashboard_' . sanitize_key( $location ) . '_' . sanitize_key( $menu_slug );
							add_action(
								$action_name,
								function () use ( $notification, $meta ) {
									$this->render_notification( $notification, $meta );
								}
							);
						}
					}
				}
			}
		}

		/**
		 * Render Notification Element.
		 *
		 * @param WP_Post $notification Notification post object.
		 * @param array   $meta         Notification metadata.
		 */
		public function render_notification( $notification, $meta ) {
			$styles     = fed_ntf_notification_styles();
			$style_key  = isset( $meta['style_type'] ) && isset( $styles[ $meta['style_type'] ] ) ? $meta['style_type'] : 'neutral';
			$is_custom  = ( 'custom' === $style_key );
			$cur_style  = $styles[ $style_key ];
			$has_close  = ( 'Enable' === $meta['close_button'] );
			$is_perm    = ( 'close_permanently' === $meta['close_action'] );
			$close_url  = $is_perm ? esc_url(
				add_query_arg(
					array(
						'action'    => 'fed_notification_close_action',
						'ntf_id'    => $notification->ID,
						'fed_nonce' => wp_create_nonce( 'fed_nonce' ),
					),
					admin_url( 'admin-ajax.php' )
				)
			) : '';

			$box_style_attr  = '';
			$icon_style_attr = '';
			$icon_class      = $cur_style['icon'];
			$icon_badge_cls  = $cur_style['badge'];
			$text_style_attr = '';

			if ( $is_custom ) {
				$bg_color     = ! empty( $meta['custom_bg_color'] ) ? $meta['custom_bg_color'] : '#f8fafc';
				$text_color   = ! empty( $meta['custom_text_color'] ) ? $meta['custom_text_color'] : '#0f172a';
				$border_color = ! empty( $meta['custom_border_color'] ) ? $meta['custom_border_color'] : '#cbd5e1';
				$icon_class   = ! empty( $meta['custom_icon'] ) ? $meta['custom_icon'] : 'fas fa-bell';

				$box_style_attr  = sprintf( 'background-color: %1$s !important; border-color: %2$s !important; color: %3$s !important;', esc_attr( $bg_color ), esc_attr( $border_color ), esc_attr( $text_color ) );
				$icon_style_attr = sprintf( 'background-color: rgba(255,255,255,0.18) !important; color: %1$s !important; border: 1px solid %2$s !important;', esc_attr( $text_color ), esc_attr( $border_color ) );
				$text_style_attr = sprintf( 'color: %s !important;', esc_attr( $text_color ) );
				$icon_badge_cls  = '';
			}

			$raw_content    = $notification->post_content;
			$content_output = wpautop( do_shortcode( wp_kses_post( $raw_content ) ) );
			?>
			<div class="fed_notification_container fed-ntf-box fed-ntf-<?php echo esc_attr( $style_key ); ?> my-3 p-4 sm:p-5 rounded-2xl border shadow-2xs transition-all duration-200"
				 id="fed_notification_<?php echo esc_attr( $notification->ID ); ?>"
				 data-id="<?php echo esc_attr( $notification->ID ); ?>"
				 data-dismiss="<?php echo $is_perm ? 'permanent' : 'session'; ?>"
				 data-close-url="<?php echo esc_url( $close_url ); ?>"
				 <?php echo ! empty( $box_style_attr ) ? 'style="' . esc_attr( $box_style_attr ) . '"' : ''; ?>>

				<div class="flex items-start justify-between gap-3.5">
					<div class="flex items-start gap-3.5 min-w-0 w-full">
						<div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm shrink-0 mt-0.5 <?php echo esc_attr( $icon_badge_cls ); ?>"
							 <?php echo ! empty( $icon_style_attr ) ? 'style="' . esc_attr( $icon_style_attr ) . '"' : ''; ?>>
							<i class="<?php echo esc_attr( $icon_class ); ?>" <?php echo ! empty( $text_style_attr ) ? 'style="' . esc_attr( $text_style_attr ) . '"' : ''; ?>></i>
						</div>
						<div class="min-w-0 space-y-1 flex-1">
							<?php if ( ! empty( $notification->post_title ) ) : ?>
								<h5 class="text-xs sm:text-sm font-bold m-0 leading-snug <?php echo $is_custom ? '' : 'text-slate-900'; ?>"
									<?php echo ! empty( $text_style_attr ) ? 'style="' . esc_attr( $text_style_attr ) . '"' : ''; ?>>
									<?php echo esc_html( $notification->post_title ); ?>
								</h5>
							<?php endif; ?>
							<div class="text-xs font-normal leading-relaxed fed-ntf-content <?php echo $is_custom ? '' : 'text-slate-700'; ?>"
								 <?php echo ! empty( $text_style_attr ) ? 'style="' . esc_attr( $text_style_attr ) . '"' : ''; ?>>
								<?php echo wp_kses_post( $content_output ); ?>
							</div>

						</div>
					</div>

					<?php if ( $has_close ) : ?>
						<button type="button"
								class="fed_notification_close_button w-7 h-7 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-black/5 flex items-center justify-center text-xs transition-colors shrink-0 border-0 bg-transparent cursor-pointer p-0"
								<?php echo ! empty( $text_style_attr ) ? 'style="' . esc_attr( $text_style_attr ) . ' opacity: 0.8;"' : ''; ?>
								aria-label="<?php esc_attr_e( 'Dismiss Notification', 'frontend-dashboard-notification' ); ?>"
								title="<?php esc_attr_e( 'Dismiss Notification', 'frontend-dashboard-notification' ); ?>">
							<i class="fas fa-times" <?php echo ! empty( $text_style_attr ) ? 'style="' . esc_attr( $text_style_attr ) . '"' : ''; ?>></i>
						</button>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}

		/**
		 * AJAX: Dismiss Notification Handler.
		 */
		public function notification_close_action() {
			$request = filter_input_array( INPUT_GET, FILTER_SANITIZE_STRING );
			if ( empty( $request ) ) {
				$request = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			}

			fed_verify_nonce( $request );

			$ntf_id = isset( $request['ntf_id'] ) ? absint( $request['ntf_id'] ) : 0;
			if ( ! $ntf_id && isset( $request['button_action'] ) ) {
				// Backward compatibility for legacy button_action parameter
				$parts  = explode( '_', $request['button_action'] );
				$ntf_id = absint( end( $parts ) );
			}

			if ( $ntf_id > 0 && is_user_logged_in() ) {
				$user_id   = get_current_user_id();
				$user_meta = get_user_meta( $user_id, 'fed_notification_user_settings', true );
				if ( ! is_array( $user_meta ) ) {
					$user_meta = array();
				}

				if ( ! in_array( $ntf_id, $user_meta, true ) ) {
					$user_meta[] = $ntf_id;
					update_user_meta( $user_id, 'fed_notification_user_settings', $user_meta );
				}
			}

			wp_send_json_success( array( 'message' => __( 'Notification dismissed.', 'frontend-dashboard-notification' ) ) );
		}
	}

	new FED_NTF_Dashboard_Notification();
}
