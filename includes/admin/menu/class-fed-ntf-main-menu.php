<?php
/**
 * Main Menu.
 *
 * @package Frontend Dashboard Notification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FED_NTF_Main_Menu' ) ) {
	/**
	 * Class FED_NTF_Main_Menu
	 */
	class FED_NTF_Main_Menu {
		/**
		 * FED_NTF_Main_Menu constructor.
		 */
		public function __construct() {
			add_filter( 'fed_add_main_sub_menu', array( $this, 'menu' ) );
		}

		/**
		 * Menu.
		 *
		 * @param  array $menu  Menu.
		 *
		 * @return array
		 */
		public function menu( $menu ) {
			$notification_menu = array(
				'fed_ntf_menu' => array(
					'page_title' => __( 'Notification', 'frontend-dashboard-notification' ),
					'menu_title' => __( 'Notification', 'frontend-dashboard-notification' ),
					'capability' => 'manage_options',
					'callback'   => array( $this, 'layout' ),
					'position'   => 10,
				),
			);

			return array_merge( $menu, $notification_menu );
		}

		/**
		 * Payment Menu Layout.
		 */
		public function layout() {
			$menus = apply_filters(
				'fed_ntf_main_menu', array(
					'settings' => array(
						'icon'    => 'fa fa-cogs',
						'name'    => __( 'General', 'frontend-dashboard-notification' ),
						'submenu' => array(
							'FED_NTF_Menu_General@layout' => array(
								'icon' => 'fa fa-cogs',
								'name' => __( 'Settings', 'frontend-dashboard-notification' ),
								'menu' => array( 'FED_NTF_Menu_General@layout' ),
							),
						),
					),
				)
			);
			?>
			<div class="bc_fed">
				<div class="container">
					<?php if ( count( $menus ) ) { ?>
						<div class="m-t-10">
							<h3 class="fed_header_font_color">
								<?php esc_attr_e( 'Notification', 'frontend-dashboard-notification' ); ?>
							</h3>
							<?php $this->header_menu( $menus ); ?>
						</div>
						<div class="m-t-10">
							<?php $this->body_content( $menus ); ?>
						</div>
					<?php } else {
						?>
						<div class="m-t-10">
							<?php esc_attr_e( 'Something went wrong', 'frontend-dashboard-notification' ); ?>
						</div>
						<?php
					} ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Menus.
		 *
		 * @param  array $menus  Menus.
		 */
		public function header_menu( $menus ) {
			$get_payload = filter_input_array( INPUT_GET, FILTER_SANITIZE_STRING );
			?>
			<ul class="nav nav-tabs" id="" role="tablist">
				<?php
				foreach ( $menus as $index => $item ) {
					$active = '';
					if ( isset( $get_payload, $get_payload['menu'] ) && $get_payload['menu'] === $index ) {
						$active = 'active';
					}
					if ( ! isset( $get_payload['menu'] ) && ( 'fed_payments' === $get_payload['page'] ) && 'gateway' === $index ) {
						$active = 'active';
					}
					?>
					<li class="<?php echo esc_attr( $active ); ?>">
						<a href="
						<?php
						echo esc_url(
							fed_menu_page_url(
								'fed_payments', array(
									'menu' => esc_attr( $index ),
								)
							)
						);
						?>
						">
							<i class="<?php echo esc_attr( fed_get_data( 'icon', $item ) ); ?>"></i>
							<?php echo esc_attr( fed_get_data( 'name', $item ) ); ?>
						</a>
					</li>
				<?php } ?>

			</ul>
			<?php
		}

		/**
		 * Body Content.
		 *
		 * @param  array $menus  Menus.
		 */
		public function body_content( $menus ) {
			$_menu    = fed_get_data( 'menu' );
			$_submenu = fed_get_data( 'submenu' );
			$menu     = ! empty( $_menu ) ? esc_html( $_menu ) : fed_get_first_key_in_array( $menus );
			$submenu  = ! empty( $_submenu ) ? esc_html( $_submenu ) : false;

			if ( $menu ) {
				if (
					isset( $menus[ $menu ]['submenu'] ) && is_array( $menus[ $menu ]['submenu'] ) && count(
						$menus[ $menu ]['submenu']
					)
				) {
					?>
					<div class="row">
						<div class="col-md-3">
							<ul class="list-group">
								<?php
								foreach ( $menus[ $menu ]['submenu'] as $index => $sub_menu ) {
									$active = '';
									if ( ! $submenu ) {
										$submenu = fed_get_first_key_in_array( $menus[ $menu ]['submenu'] );
									}
									if ( in_array( $submenu, $sub_menu['menu'] ) ) {
										$active = 'active';
									}
									?>
									<li class="list-group-item <?php echo esc_attr( $active ); ?>">
										<a href="
										<?php
										echo esc_url(
											fed_menu_page_url(
												'fed_payments', array(
													'menu'    => $menu,
													'submenu' => $index,
												)
											)
										);
										?>
										">
											<i class="<?php echo esc_attr( $sub_menu['icon'] ); ?>"></i>
											<?php echo esc_attr( $sub_menu['name'] ); ?>
										</a>
									</li>
								<?php } ?>
							</ul>
						</div>
						<div class="col-md-9">
							<div class="panel panel-primary">
								<div class="panel-heading">
									<h3 class="panel-title">
										<i class="
										<?php
										echo esc_attr(
											$menus[ $menu ]['submenu'][ $submenu ]['icon']
										);
										?>
										"></i>
										<?php echo esc_attr( $menus[ $menu ]['submenu'][ $submenu ]['name'] ); ?>
									</h3>
								</div>
								<div class="panel-body">
									<?php
									if ( is_string( $submenu ) ) {
										fed_execute_method_by_string( $submenu, $_GET );
									}
									?>
								</div>
							</div>
						</div>
					</div>
					<?php
				} else {
					if ( isset( $menus[ $menu ]['submenu'] ) && is_string( $menus[ $menu ]['submenu'] ) ) {
						$sub_menu_action = $menus[ $menu ]['submenu'];
						$extra_label     = '';
						if ( isset( $menus[ $menu ]['routes'] ) && is_array( $menus[ $menu ]['routes'] ) ) {
							$route_value = fed_get_data( 'route', $_GET, false );
							if ( array_key_exists( $route_value, $menus[ $menu ]['routes'] ) ) {
								$sub_menu_action = $route_value;
								$extra_label     = ' - ' . fed_get_data(
										$menu . '.routes.' . $route_value . '.name', $menus
									);
							}
						}
						?>
						<div class="row">
							<div class="col-md-12">
								<div class="panel panel-primary">
									<div class="panel-heading">
										<h3 class="panel-title">
											<i class="<?php echo esc_attr( $menus[ $menu ]['icon'] ); ?>"></i>
											<?php echo esc_attr( $menus[ $menu ]['name'] . $extra_label ); ?></h3>
									</div>
									<div class="panel-body">
										<?php fed_execute_method_by_string( $sub_menu_action, $_GET ); ?>
									</div>
								</div>

							</div>
						</div>
						<?php
					} elseif ( isset( $menus[ $menu ] ) ) {
						?>
						<div class="row">
							<div class="col-md-12">
								<?php esc_attr_e( 'Under Construction', 'frontend-dashboard-notification' ); ?>
							</div>
						</div>
						<?php
					} else { ?>
						<div class="alert alert-danger">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<strong>
								<?php
								esc_attr_e(
									'Invalid Menu Item | Error FED|Admin|Payment|FEDPaymentMenu@body_content',
									'frontend-dashboard-notification'
								);
								?>
							</strong>
						</div>
						<?php
						// FED_Log::writeLog( 'Invalid Menu Item | Error FED|Admin|Payment|FEDPaymentMenu@body_content' );
					}
				}
			} else {
				?>
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-danger">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<strong>
								<?php
								esc_attr_e(
									'Ah! something missing | Error FED|Admin|Payment|FEDPaymentMenu@body_content',
									'frontend-dashboard-notification'
								);
								?>
							</strong>
						</div>
						<?php
						/** FED_Log::writeLog(
						 * 'Ah! something missing | Error FED|Admin|Payment|FEDPaymentMenu@body_content'
						 * ); **/
						?>
					</div>
				</div>
				<?php
			}
		}
	}

	new FED_NTF_Main_Menu();
}