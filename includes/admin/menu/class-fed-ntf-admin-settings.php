<?php
/**
 * Frontend Dashboard Notification Admin Settings.
 *
 * @package Frontend Dashboard Notification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FED_NTF_Admin_Settings' ) ) {
	/**
	 * Class FED_NTF_Admin_Settings
	 */
	class FED_NTF_Admin_Settings {

		/**
		 * FED_NTF_Admin_Settings constructor.
		 */
		public function __construct() {
			// Add submenu item under Frontend Dashboard menu
			add_filter( 'fed_add_main_sub_menu', array( $this, 'fed_ntf_add_main_sub_menu' ) );

			// Add tab inside Frontend Dashboard Settings panel
			add_filter(
				'fed_admin_dashboard_settings_menu_header',
				array( $this, 'fed_ntf_admin_dashboard_settings_menu_header' )
			);

			add_filter( 'fed_plugin_versions', array( $this, 'fed_ntf_plugin_version' ) );

			// AJAX handlers for notification management
			add_action( 'wp_ajax_fed_ntf_save_notification', array( $this, 'ajax_save_notification' ) );
			add_action( 'wp_ajax_fed_ntf_delete_notification', array( $this, 'ajax_delete_notification' ) );
			add_action( 'wp_ajax_fed_ntf_toggle_status', array( $this, 'ajax_toggle_status' ) );
		}

		/**
		 * Add Notifications submenu directly under Frontend Dashboard menu.
		 *
		 * @param array $menu Submenus array.
		 * @return array
		 */
		public function fed_ntf_add_main_sub_menu( $menu ) {
			$menu['fed_notification'] = array(
				'page_title' => __( 'Notifications', 'frontend-dashboard-notification' ),
				'menu_title' => __( 'Notifications', 'frontend-dashboard-notification' ),
				'capability' => 'manage_options',
				'callback'   => array( $this, 'fed_ntf_standalone_page' ),
				'position'   => 45,
			);

			return $menu;
		}

		/**
		 * Standalone Admin Page Wrapper.
		 */
		public function fed_ntf_standalone_page() {
			?>
			<div class="wrap fed-admin-wrap" style="width: calc(100% - 20px); max-width: 100%; margin: 20px 20px 40px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif; box-sizing: border-box;">
				<?php $this->fed_ntf_show_admin_settings(); ?>
			</div>
			<?php
		}

		/**
		 * Register Notification tab in Frontend Dashboard Settings.
		 *
		 * @param array $menu Header menus.
		 * @return array
		 */
		public function fed_ntf_admin_dashboard_settings_menu_header( $menu ) {
			return array_merge(
				$menu,
				array(
					'notification' => array(
						'icon_class' => 'fas fa-bell',
						'name'       => __( 'Notification', 'frontend-dashboard-notification' ),
						'callable'   => array(
							'object' => $this,
							'method' => 'fed_ntf_show_admin_settings',
						),
					),
				)
			);
		}

		/**
		 * Register plugin version info.
		 *
		 * @param array $versions Plugin versions.
		 * @return array
		 */
		public function fed_ntf_plugin_version( $versions ) {
			return array_merge(
				$versions,
				array(
					'notification' => sprintf(
						/* translators: %s: Version number */
						__( 'Notification (%s)', 'frontend-dashboard-notification' ),
						BC_FED_NTF_PLUGIN_VERSION
					),
				)
			);
		}

		/**
		 * Show Notification Settings Main Layout.
		 */
		public function fed_ntf_show_admin_settings() {
			$action = isset( $_GET['ntf_action'] ) ? sanitize_text_field( wp_unslash( $_GET['ntf_action'] ) ) : 'list';
			$ntf_id = isset( $_GET['ntf_id'] ) ? absint( $_GET['ntf_id'] ) : 0;

			if ( 'add' === $action || 'edit' === $action ) {
				$this->render_form_view( $ntf_id );
			} else {
				$this->render_list_view();
			}
		}

		/**
		 * Render Notifications List View with Pagination.
		 */
		public function render_list_view() {
			$paged         = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
			$per_page      = 10;
			$query         = fed_ntf_get_notifications_query( $paged, $per_page );
			$notifications = $query->posts;
			$total_count   = $query->found_posts;
			$total_pages   = $query->max_num_pages;

			// Get all counts for status metrics
			$all_items    = fed_ntf_get_all_notifications();
			$active_count = 0;
			foreach ( $all_items as $ntf ) {
				$st = get_post_meta( $ntf->ID, 'fed_ntf_notification_status', true );
				if ( 'Enable' === $st || empty( $st ) ) {
					$active_count++;
				}
			}
			$disabled_count = count( $all_items ) - $active_count;

			$styles        = fed_ntf_notification_styles();
			$all_locations = fed_ntf_notification_locations();
			$all_menus_raw = fed_get_all_dashboard_display_menus();
			$menu_items    = wp_list_pluck( $all_menus_raw, 'menu', 'menu_slug' );
			$all_roles     = fed_get_user_roles();
			?>
			<div class="fed-ntf-admin-container space-y-6">
				<!-- Header Banner -->
				<div class="p-6 bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 rounded-3xl text-white shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
					<div class="flex items-center gap-4">
						<div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-xl text-indigo-200 shrink-0 border border-white/15">
							<i class="fas fa-bell"></i>
						</div>
						<div>
							<h3 class="text-base sm:text-lg font-bold text-white m-0">
								<?php esc_html_e( 'Dashboard Notifications', 'frontend-dashboard-notification' ); ?>
							</h3>
							<p class="text-xs text-indigo-200/90 m-0 mt-0.5">
								<?php esc_html_e( 'Create and manage broadcast alerts, banners, and announcements across Frontend Dashboard pages.', 'frontend-dashboard-notification' ); ?>
							</p>
						</div>
					</div>
					<div class="flex items-center gap-3 shrink-0">
						<a href="<?php echo esc_url( add_query_arg( array( 'ntf_action' => 'add' ) ) ); ?>"
						   class="fed-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-xs no-underline cursor-pointer"
						   style="background-color: #4f46e5 !important; color: #ffffff !important;">
							<i class="fas fa-plus" style="color: #ffffff !important;"></i>
							<span style="color: #ffffff !important;"><?php esc_html_e( 'Add Notification', 'frontend-dashboard-notification' ); ?></span>
						</a>
					</div>
				</div>

				<!-- Metric Badges -->
				<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
					<div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
						<div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-sm shrink-0">
							<i class="fas fa-layer-group"></i>
						</div>
						<div>
							<div class="text-[11px] font-bold uppercase tracking-wider text-slate-400"><?php esc_html_e( 'Total Notifications', 'frontend-dashboard-notification' ); ?></div>
							<div class="text-lg font-bold text-slate-800"><?php echo esc_html( count( $all_items ) ); ?></div>
						</div>
					</div>

					<div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
						<div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm shrink-0">
							<i class="fas fa-check-circle"></i>
						</div>
						<div>
							<div class="text-[11px] font-bold uppercase tracking-wider text-slate-400"><?php esc_html_e( 'Active Alerts', 'frontend-dashboard-notification' ); ?></div>
							<div class="text-lg font-bold text-emerald-600"><?php echo esc_html( $active_count ); ?></div>
						</div>
					</div>

					<div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
						<div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm shrink-0">
							<i class="fas fa-pause-circle"></i>
						</div>
						<div>
							<div class="text-[11px] font-bold uppercase tracking-wider text-slate-400"><?php esc_html_e( 'Disabled', 'frontend-dashboard-notification' ); ?></div>
							<div class="text-lg font-bold text-amber-600"><?php echo esc_html( $disabled_count ); ?></div>
						</div>
					</div>
				</div>

				<!-- Notifications Table or Empty State -->
				<?php if ( ! empty( $notifications ) ) : ?>
					<div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden">
						<table class="w-full text-left border-collapse" id="fed_ntf_table" style="table-layout: fixed; width: 100%;">
							<thead>
								<tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
									<th class="py-3.5 px-4" style="width: 28%;"><?php esc_html_e( 'Notification', 'frontend-dashboard-notification' ); ?></th>
									<th class="py-3.5 px-3" style="width: 14%;"><?php esc_html_e( 'Type', 'frontend-dashboard-notification' ); ?></th>
									<th class="py-3.5 px-3" style="width: 16%;"><?php esc_html_e( 'Location', 'frontend-dashboard-notification' ); ?></th>
									<th class="py-3.5 px-3" style="width: 14%;"><?php esc_html_e( 'Menus', 'frontend-dashboard-notification' ); ?></th>
									<th class="py-3.5 px-3" style="width: 14%;"><?php esc_html_e( 'Roles', 'frontend-dashboard-notification' ); ?></th>
									<th class="py-3.5 px-2 text-center" style="width: 7%;"><?php esc_html_e( 'Status', 'frontend-dashboard-notification' ); ?></th>
									<th class="py-3.5 px-4 text-right" style="width: 7%;"><?php esc_html_e( 'Actions', 'frontend-dashboard-notification' ); ?></th>
								</tr>
							</thead>
							<tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
								<?php
								foreach ( $notifications as $item ) :
									$meta       = fed_ntf_get_notification_meta( $item->ID );
									$style_key  = isset( $meta['style_type'] ) && isset( $styles[ $meta['style_type'] ] ) ? $meta['style_type'] : 'neutral';
									$cur_style  = $styles[ $style_key ];
									$is_active  = ( 'Enable' === $meta['status'] );
									$edit_url   = add_query_arg(
										array(
											'ntf_action' => 'edit',
											'ntf_id'     => $item->ID,
										)
									);

									// Compact Location text/badge
									$loc_count  = is_array( $meta['locations'] ) ? count( $meta['locations'] ) : 0;
									$loc_labels = array();
									if ( is_array( $meta['locations'] ) ) {
										foreach ( $meta['locations'] as $loc ) {
											$loc_labels[] = isset( $all_locations[ $loc ] ) ? $all_locations[ $loc ] : ucwords( str_replace( '_', ' ', $loc ) );
										}
									}
									$loc_full_text = ! empty( $loc_labels ) ? implode( ', ', $loc_labels ) : __( 'All Locations', 'frontend-dashboard-notification' );

									if ( $loc_count >= count( $all_locations ) || in_array( 'all', $meta['locations'], true ) ) {
										$loc_display = '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">' . esc_html__( 'All Slots (6)', 'frontend-dashboard-notification' ) . '</span>';
									} elseif ( $loc_count > 1 ) {
										$first_loc   = $loc_labels[0];
										$loc_display = '<span class="truncate block text-[11px] text-slate-700 font-semibold" title="' . esc_attr( $loc_full_text ) . '">' . esc_html( $first_loc ) . ' <span class="text-indigo-600 font-bold">+' . ( $loc_count - 1 ) . '</span></span>';
									} else {
										$loc_display = '<span class="truncate block text-[11px] text-slate-700 font-semibold" title="' . esc_attr( $loc_full_text ) . '">' . esc_html( $loc_full_text ) . '</span>';
									}

									// Compact Menu text/badge
									$menu_count    = is_array( $meta['menus'] ) ? count( $meta['menus'] ) : 0;
									$is_all_menus  = in_array( 'all', $meta['menus'], true ) || empty( $meta['menus'] ) || $menu_count >= count( $menu_items );
									$menu_full_text = $is_all_menus ? __( 'All Menus', 'frontend-dashboard-notification' ) : implode( ', ', array_map( 'ucfirst', $meta['menus'] ) );

									if ( $is_all_menus ) {
										$menu_display = '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">' . esc_html__( 'All Menus', 'frontend-dashboard-notification' ) . '</span>';
									} elseif ( $menu_count > 1 ) {
										$first_menu   = ucfirst( $meta['menus'][0] );
										$menu_display = '<span class="truncate block text-[11px] text-slate-700 font-semibold" title="' . esc_attr( $menu_full_text ) . '">' . esc_html( $first_menu ) . ' <span class="text-indigo-600 font-bold">+' . ( $menu_count - 1 ) . '</span></span>';
									} else {
										$menu_display = '<span class="truncate block text-[11px] text-slate-700 font-semibold" title="' . esc_attr( $menu_full_text ) . '">' . esc_html( $menu_full_text ) . '</span>';
									}

									// Compact Role text/badge
									$role_count    = is_array( $meta['user_roles'] ) ? count( $meta['user_roles'] ) : 0;
									$is_all_roles  = in_array( 'all', $meta['user_roles'], true ) || empty( $meta['user_roles'] ) || $role_count >= count( $all_roles );
									$role_full_text = $is_all_roles ? __( 'All Roles', 'frontend-dashboard-notification' ) : implode( ', ', array_map( 'ucfirst', $meta['user_roles'] ) );

									if ( $is_all_roles ) {
										$role_display = '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200/80">' . esc_html__( 'All Roles', 'frontend-dashboard-notification' ) . '</span>';
									} elseif ( $role_count > 1 ) {
										$first_role   = ucfirst( $meta['user_roles'][0] );
										$role_display = '<span class="truncate block text-[11px] text-slate-700 font-semibold" title="' . esc_attr( $role_full_text ) . '">' . esc_html( $first_role ) . ' <span class="text-indigo-600 font-bold">+' . ( $role_count - 1 ) . '</span></span>';
									} else {
										$role_display = '<span class="truncate block text-[11px] text-slate-700 font-semibold" title="' . esc_attr( $role_full_text ) . '">' . esc_html( $role_full_text ) . '</span>';
									}
									?>
									<tr class="hover:bg-slate-50/60 transition-colors" id="fed_ntf_row_<?php echo esc_attr( $item->ID ); ?>">
										<!-- Title & Content Excerpt -->
										<td class="py-3.5 px-4 overflow-hidden">
											<div class="font-bold text-slate-900 text-xs truncate">
												<?php echo esc_html( ! empty( $item->post_title ) ? $item->post_title : __( '(Untitled Notification)', 'frontend-dashboard-notification' ) ); ?>
											</div>
											<div class="text-[11px] text-slate-400 truncate mt-0.5">
												<?php echo esc_html( wp_strip_all_tags( $item->post_content ) ); ?>
											</div>
										</td>

										<!-- Style / Type -->
										<td class="py-3.5 px-3 overflow-hidden">
											<?php if ( 'custom' === $meta['style_type'] ) : ?>
												<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold truncate"
													  style="background-color: <?php echo esc_attr( $meta['custom_bg_color'] ); ?>; color: <?php echo esc_attr( $meta['custom_text_color'] ); ?>; border: 1px solid <?php echo esc_attr( $meta['custom_border_color'] ); ?>;">
													<i class="<?php echo esc_attr( $meta['custom_icon'] ); ?> text-[9px] shrink-0"></i>
													<span class="truncate"><?php esc_html_e( 'Custom', 'frontend-dashboard-notification' ); ?></span>
												</span>
											<?php else : ?>
												<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold truncate <?php echo esc_attr( $cur_style['badge'] ); ?>">
													<i class="<?php echo esc_attr( $cur_style['icon'] ); ?> text-[9px] shrink-0"></i>
													<span class="truncate"><?php echo esc_html( $cur_style['label'] ); ?></span>
												</span>
											<?php endif; ?>
										</td>

										<!-- Locations -->
										<td class="py-3.5 px-3 overflow-hidden">
											<?php echo wp_kses( $loc_display, array( 'span' => array( 'class' => array(), 'title' => array() ) ) ); ?>
										</td>

										<!-- Menus -->
										<td class="py-3.5 px-3 overflow-hidden">
											<?php echo wp_kses( $menu_display, array( 'span' => array( 'class' => array(), 'title' => array() ) ) ); ?>
										</td>

										<!-- Roles -->
										<td class="py-3.5 px-3 overflow-hidden">
											<?php echo wp_kses( $role_display, array( 'span' => array( 'class' => array(), 'title' => array() ) ) ); ?>
										</td>

										<!-- Status Toggle Switch -->
										<td class="py-3.5 px-2 text-center overflow-hidden">
											<button type="button"
													class="fed-ntf-toggle-btn inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold transition-all border cursor-pointer <?php echo $is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200'; ?>"
													data-id="<?php echo esc_attr( $item->ID ); ?>"
													data-nonce="<?php echo esc_attr( wp_create_nonce( 'fed_nonce' ) ); ?>">
												<span class="fed-ntf-status-dot w-1.5 h-1.5 rounded-full <?php echo $is_active ? 'bg-emerald-500' : 'bg-slate-400'; ?>"></span>
												<span class="fed-ntf-status-label"><?php echo $is_active ? esc_html__( 'Active', 'frontend-dashboard-notification' ) : esc_html__( 'Off', 'frontend-dashboard-notification' ); ?></span>
											</button>
										</td>

										<!-- Actions -->
										<td class="py-3.5 px-4 text-right overflow-hidden">
											<div class="flex items-center justify-end gap-1.5">
												<a href="<?php echo esc_url( $edit_url ); ?>"
												   class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 flex items-center justify-center text-xs transition-colors no-underline"
												   title="<?php esc_attr_e( 'Edit Notification', 'frontend-dashboard-notification' ); ?>">
													<i class="fas fa-edit"></i>
												</a>
												<button type="button"
														class="fed-ntf-delete-btn w-7 h-7 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 flex items-center justify-center text-xs transition-colors border-0 cursor-pointer"
														data-id="<?php echo esc_attr( $item->ID ); ?>"
														data-title="<?php echo esc_attr( $item->post_title ); ?>"
														data-nonce="<?php echo esc_attr( wp_create_nonce( 'fed_nonce' ) ); ?>"
														title="<?php esc_attr_e( 'Delete Notification', 'frontend-dashboard-notification' ); ?>">
													<i class="fas fa-trash-alt"></i>
												</button>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>

						<!-- Pagination Footer -->
						<?php if ( $total_pages > 1 ) : ?>
							<div class="px-5 py-3.5 bg-slate-50/70 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
								<div>
									<?php
									$start = ( ( $paged - 1 ) * $per_page ) + 1;
									$end   = min( $paged * $per_page, $total_count );
									printf(
										/* translators: 1: start, 2: end, 3: total */
										esc_html__( 'Showing %1$s to %2$s of %3$s notifications', 'frontend-dashboard-notification' ),
										esc_html( (string) $start ),
										esc_html( (string) $end ),
										esc_html( (string) $total_count )
									);
									?>

								</div>
								<div class="flex items-center gap-1.5">
									<?php if ( $paged > 1 ) : ?>
										<a href="<?php echo esc_url( add_query_arg( 'paged', $paged - 1 ) ); ?>"
										   class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 font-semibold text-xs transition-colors no-underline">
											<i class="fas fa-chevron-left mr-1 text-[9px]"></i> <?php esc_html_e( 'Prev', 'frontend-dashboard-notification' ); ?>
										</a>
									<?php endif; ?>

									<?php
									for ( $i = 1; $i <= $total_pages; $i++ ) :
										$is_curr = ( $i === $paged );
										?>
										<a href="<?php echo esc_url( add_query_arg( 'paged', $i ) ); ?>"
										   class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs transition-colors no-underline <?php echo $is_curr ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100'; ?>"
										   <?php echo $is_curr ? 'style="color: #ffffff !important;"' : ''; ?>>
											<?php echo esc_html( $i ); ?>
										</a>
									<?php endfor; ?>

									<?php if ( $paged < $total_pages ) : ?>
										<a href="<?php echo esc_url( add_query_arg( 'paged', $paged + 1 ) ); ?>"
										   class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 font-semibold text-xs transition-colors no-underline">
											<?php esc_html_e( 'Next', 'frontend-dashboard-notification' ); ?> <i class="fas fa-chevron-right ml-1 text-[9px]"></i>
										</a>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<!-- Empty State -->
					<div class="bg-white rounded-3xl p-12 text-center border border-slate-200/90 shadow-xs space-y-4">
						<div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl mx-auto">
							<i class="fas fa-bell-slash"></i>
						</div>
						<div>
							<h4 class="text-sm sm:text-base font-bold text-slate-800 m-0"><?php esc_html_e( 'No Notifications Created Yet', 'frontend-dashboard-notification' ); ?></h4>
							<p class="text-xs text-slate-500 m-0 mt-1 max-w-md mx-auto">
								<?php esc_html_e( 'Broadcast essential announcements, onboarding tips, or promotions to users across Frontend Dashboard menus.', 'frontend-dashboard-notification' ); ?>
							</p>
						</div>
						<div class="pt-2">
							<a href="<?php echo esc_url( add_query_arg( array( 'ntf_action' => 'add' ) ) ); ?>"
							   class="fed-btn-primary inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold shadow-xs transition-all no-underline cursor-pointer"
							   style="background-color: #4f46e5 !important; color: #ffffff !important;">
								<i class="fas fa-plus" style="color: #ffffff !important;"></i>
								<span style="color: #ffffff !important;"><?php esc_html_e( 'Create Your First Notification', 'frontend-dashboard-notification' ); ?></span>
							</a>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}

		/**
		 * Render Add / Edit Notification Form View.
		 *
		 * @param int $post_id Post ID for edit mode (0 for add).
		 */
		public function render_form_view( $post_id = 0 ) {
			$is_edit      = ( $post_id > 0 );
			$post         = $is_edit ? get_post( $post_id ) : null;
			$title        = $post ? $post->post_title : '';
			$content      = $post ? $post->post_content : '';
			$meta         = $is_edit ? fed_ntf_get_notification_meta( $post_id ) : array(
				'status'              => 'Enable',
				'locations'           => array( 'content_top' ),
				'menus'               => array( 'all' ),
				'user_roles'          => array( 'all' ),
				'close_button'        => 'Enable',
				'close_action'        => 'close_permanently',
				'style_type'          => 'neutral',
				'custom_bg_color'     => '#e0e7ff',
				'custom_text_color'   => '#1e1b4b',
				'custom_border_color' => '#818cf8',
				'custom_icon'         => 'fas fa-bell',
			);

			$locations     = fed_ntf_notification_locations();
			$styles        = fed_ntf_notification_styles();
			$all_menus_raw = fed_get_all_dashboard_display_menus();
			$menu_items    = wp_list_pluck( $all_menus_raw, 'menu', 'menu_slug' );
			$all_roles     = fed_get_user_roles();

			$list_url     = remove_query_arg( array( 'ntf_action', 'ntf_id' ) );
			$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'fed_notification';
			?>
			<div class="fed-ntf-admin-container space-y-6">
				<!-- Header Navigation -->
				<div class="p-6 bg-white rounded-3xl border border-slate-200/90 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
					<div class="flex items-center gap-3.5">
						<a href="<?php echo esc_url( $list_url ); ?>"
						   class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 flex items-center justify-center text-sm transition-colors no-underline">
							<i class="fas fa-arrow-left"></i>
						</a>
						<div>
							<h3 class="text-sm sm:text-base font-bold text-slate-900 m-0">
								<?php echo $is_edit ? esc_html__( 'Edit Notification', 'frontend-dashboard-notification' ) : esc_html__( 'Create New Notification', 'frontend-dashboard-notification' ); ?>
							</h3>
							<p class="text-xs text-slate-400 m-0 mt-0.5">
								<?php esc_html_e( 'Configure alert copy, target slot position, menu filters, and target user roles.', 'frontend-dashboard-notification' ); ?>
							</p>
						</div>
					</div>
					<div class="flex items-center gap-2">
						<a href="<?php echo esc_url( $list_url ); ?>"
						   class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-all no-underline cursor-pointer">
							<?php esc_html_e( 'Cancel', 'frontend-dashboard-notification' ); ?>
						</a>
					</div>
				</div>

				<!-- Notification Edit Form -->
				<form method="post"
					  id="fed_ntf_edit_form"
					  class="fed_admin_menu fed_ajax space-y-6"
					  action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=fed_ntf_save_notification' ) ); ?>">

					<?php wp_nonce_field( 'fed_nonce', 'fed_nonce' ); ?>
					<?php echo fed_loader(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

					<input type="hidden" name="notification_id" value="<?php echo esc_attr( $post_id ); ?>" />
					<input type="hidden" name="current_page" value="<?php echo esc_attr( $current_page ); ?>" />

					<!-- Card 1: Content & Copy -->
					<div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-xs space-y-5">
						<div class="pb-3 border-b border-slate-100 flex items-center gap-2.5">
							<div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
								<i class="fas fa-pen"></i>
							</div>
							<div>
								<h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 m-0"><?php esc_html_e( 'Notification Content', 'frontend-dashboard-notification' ); ?></h4>
								<p class="text-[11px] text-slate-400 m-0"><?php esc_html_e( 'Enter the headline and message body to display.', 'frontend-dashboard-notification' ); ?></p>
							</div>
						</div>

						<div class="space-y-4">
							<!-- Title Field -->
							<div class="space-y-1.5">
								<label for="fed_ntf_title" class="block text-xs font-bold text-slate-800">
									<?php esc_html_e( 'Notification Title / Headline', 'frontend-dashboard-notification' ); ?> <span class="text-rose-500">*</span>
								</label>
								<input type="text"
									   name="fed_notification_title"
									   id="fed_ntf_title"
									   required
									   value="<?php echo esc_attr( $title ); ?>"
									   placeholder="<?php esc_attr_e( 'e.g., Scheduled Maintenance Announcement', 'frontend-dashboard-notification' ); ?>"
									   class="w-full px-4 py-2.5 text-xs text-slate-800 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-500 transition-all outline-none" />
							</div>

							<!-- Content Field (WYSIWYG Editor with Media Support) -->
							<div class="space-y-1.5">
								<label for="fed_notification_content" class="block text-xs font-bold text-slate-800">
									<?php esc_html_e( 'Message Body / Details', 'frontend-dashboard-notification' ); ?> <span class="text-rose-500">*</span>
								</label>
								<div class="fed-wp-editor-wrap bg-slate-50/70 border border-slate-200 rounded-2xl p-2.5 focus-within:border-indigo-500 focus-within:bg-white transition-all">
									<?php
									$editor_settings = array(
										'textarea_name' => 'fed_notification_content',
										'textarea_rows' => 8,
										'media_buttons' => true,
										'teeny'         => false,
										'quicktags'     => true,
										'editor_class'  => 'w-full text-xs text-slate-800',
									);
									wp_editor( $content, 'fed_notification_content', $editor_settings );
									?>
								</div>
								<p class="text-[11px] text-slate-400 m-0"><?php esc_html_e( 'Rich text formatting, links, images, and media embeds are fully supported.', 'frontend-dashboard-notification' ); ?></p>
							</div>
						</div>
					</div>

					<!-- Card 2: Visual Style & Appearance -->
					<div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-xs space-y-5">
						<div class="pb-3 border-b border-slate-100 flex items-center gap-2.5">
							<div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
								<i class="fas fa-palette"></i>
							</div>
							<div>
								<h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 m-0"><?php esc_html_e( 'Alert Style & Theme', 'frontend-dashboard-notification' ); ?></h4>
								<p class="text-[11px] text-slate-400 m-0"><?php esc_html_e( 'Select a preset theme or customize exact background, border, and text colors.', 'frontend-dashboard-notification' ); ?></p>
							</div>
						</div>

						<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
							<?php
							foreach ( $styles as $key => $style ) :
								$is_checked = ( $meta['style_type'] === $key );
								?>
								<label class="fed-ntf-style-card p-4 rounded-2xl border transition-all cursor-pointer flex items-start gap-3 <?php echo $is_checked ? 'bg-indigo-50/80 border-indigo-300 ring-2 ring-indigo-500/20 shadow-xs' : 'bg-slate-50/70 border-slate-200/80 hover:bg-slate-100/70'; ?>">
									<input type="radio"
										   name="fed_notification[style_type]"
										   value="<?php echo esc_attr( $key ); ?>"
										   class="sr-only fed-ntf-style-radio"
										   <?php checked( $is_checked, true ); ?> />
									<div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-sm <?php echo esc_attr( $style['badge'] ); ?>">
										<i class="<?php echo esc_attr( $style['icon'] ); ?>"></i>
									</div>
									<div class="min-w-0">
										<div class="text-xs font-bold text-slate-900"><?php echo esc_html( $style['label'] ); ?></div>
										<p class="text-[11px] text-slate-500 m-0 mt-0.5">
											<?php
											if ( 'custom' === $key ) {
												esc_html_e( 'Define custom colors to match your website theme.', 'frontend-dashboard-notification' );
											} else {
												/* translators: %s: Style label */
												echo esc_html( sprintf( __( 'Displays with %s styling & icon.', 'frontend-dashboard-notification' ), strtolower( $style['label'] ) ) );
											}
											?>
										</p>
									</div>
								</label>
							<?php endforeach; ?>
						</div>


						<!-- Custom Color Palette Configuration (Shown when "Custom Theme Color" is selected) -->
						<div id="fed_ntf_custom_color_panel" class="<?php echo ( 'custom' === $meta['style_type'] ) ? '' : 'hidden'; ?> p-5 bg-slate-50/90 rounded-2xl border border-slate-200/90 space-y-4 transition-all">
							<div class="flex items-center justify-between pb-2 border-b border-slate-200/70">
								<div class="flex items-center gap-2">
									<i class="fas fa-sliders-h text-indigo-600 text-xs"></i>
									<span class="text-xs font-bold text-slate-800"><?php esc_html_e( 'Custom Palette & Icon Options', 'frontend-dashboard-notification' ); ?></span>
								</div>
								<span class="text-[11px] text-slate-400"><?php esc_html_e( 'Match your exact brand hex codes', 'frontend-dashboard-notification' ); ?></span>
							</div>

							<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
								<!-- Background Color -->
								<div class="space-y-1.5">
									<label class="block text-[11px] font-bold text-slate-700"><?php esc_html_e( 'Background Color', 'frontend-dashboard-notification' ); ?></label>
									<div class="flex items-center gap-2 bg-white px-2.5 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
										<input type="color"
											   id="fed_ntf_picker_bg"
											   value="<?php echo esc_attr( $meta['custom_bg_color'] ); ?>"
											   class="w-7 h-7 rounded-lg border-0 cursor-pointer p-0 bg-transparent" />
										<input type="text"
											   name="fed_notification[custom_bg_color]"
											   id="fed_ntf_input_bg"
											   value="<?php echo esc_attr( $meta['custom_bg_color'] ); ?>"
											   placeholder="#e0e7ff"
											   maxlength="7"
											   class="w-full text-xs font-mono text-slate-700 uppercase outline-none border-0 p-0" />
									</div>
								</div>

								<!-- Text Color -->
								<div class="space-y-1.5">
									<label class="block text-[11px] font-bold text-slate-700"><?php esc_html_e( 'Text & Title Color', 'frontend-dashboard-notification' ); ?></label>
									<div class="flex items-center gap-2 bg-white px-2.5 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
										<input type="color"
											   id="fed_ntf_picker_text"
											   value="<?php echo esc_attr( $meta['custom_text_color'] ); ?>"
											   class="w-7 h-7 rounded-lg border-0 cursor-pointer p-0 bg-transparent" />
										<input type="text"
											   name="fed_notification[custom_text_color]"
											   id="fed_ntf_input_text"
											   value="<?php echo esc_attr( $meta['custom_text_color'] ); ?>"
											   placeholder="#1e1b4b"
											   maxlength="7"
											   class="w-full text-xs font-mono text-slate-700 uppercase outline-none border-0 p-0" />
									</div>
								</div>

								<!-- Border Color -->
								<div class="space-y-1.5">
									<label class="block text-[11px] font-bold text-slate-700"><?php esc_html_e( 'Border / Stroke Color', 'frontend-dashboard-notification' ); ?></label>
									<div class="flex items-center gap-2 bg-white px-2.5 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
										<input type="color"
											   id="fed_ntf_picker_border"
											   value="<?php echo esc_attr( $meta['custom_border_color'] ); ?>"
											   class="w-7 h-7 rounded-lg border-0 cursor-pointer p-0 bg-transparent" />
										<input type="text"
											   name="fed_notification[custom_border_color]"
											   id="fed_ntf_input_border"
											   value="<?php echo esc_attr( $meta['custom_border_color'] ); ?>"
											   placeholder="#818cf8"
											   maxlength="7"
											   class="w-full text-xs font-mono text-slate-700 uppercase outline-none border-0 p-0" />
									</div>
								</div>

								<!-- Custom Icon Picker -->
								<div class="space-y-1.5">
									<label class="block text-[11px] font-bold text-slate-700"><?php esc_html_e( 'Icon Symbol', 'frontend-dashboard-notification' ); ?></label>
									<select name="fed_notification[custom_icon]"
											id="fed_ntf_select_icon"
											class="w-full px-3 py-2 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-indigo-500 transition-all cursor-pointer">
										<?php
										$icon_options = array(
											'fas fa-bell'                 => __( 'Bell (Default)', 'frontend-dashboard-notification' ),
											'fas fa-bullhorn'             => __( 'Bullhorn (Announcement)', 'frontend-dashboard-notification' ),
											'fas fa-info-circle'          => __( 'Info Circle', 'frontend-dashboard-notification' ),
											'fas fa-check-circle'         => __( 'Check Circle', 'frontend-dashboard-notification' ),
											'fas fa-exclamation-triangle' => __( 'Warning Triangle', 'frontend-dashboard-notification' ),
											'fas fa-star'                 => __( 'Star (Featured)', 'frontend-dashboard-notification' ),
											'fas fa-rocket'               => __( 'Rocket (Launch)', 'frontend-dashboard-notification' ),
											'fas fa-gift'                 => __( 'Gift (Promotion)', 'frontend-dashboard-notification' ),
											'fas fa-tag'                  => __( 'Tag (Discount)', 'frontend-dashboard-notification' ),
											'fas fa-shield-alt'           => __( 'Shield (Security)', 'frontend-dashboard-notification' ),
											'fas fa-heart'                => __( 'Heart (Appreciation)', 'frontend-dashboard-notification' ),
											'fas fa-bolt'                 => __( 'Lightning Bolt', 'frontend-dashboard-notification' ),
											'fas fa-lightbulb'            => __( 'Lightbulb (Tip)', 'frontend-dashboard-notification' ),
											'fas fa-crown'                => __( 'Crown (VIP)', 'frontend-dashboard-notification' ),
										);
										foreach ( $icon_options as $ico_class => $ico_label ) :
											?>
											<option value="<?php echo esc_attr( $ico_class ); ?>" <?php selected( $meta['custom_icon'], $ico_class ); ?>>
												<?php echo esc_html( $ico_label ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>

							<!-- Live Real-time Preview Box -->
							<div class="pt-2">
								<div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5"><?php esc_html_e( 'Real-time Live Preview', 'frontend-dashboard-notification' ); ?></div>
								<div id="fed_ntf_live_preview"
									 class="p-4 rounded-2xl border shadow-2xs flex items-start gap-3.5 transition-all"
									 style="background-color: <?php echo esc_attr( $meta['custom_bg_color'] ); ?>; border-color: <?php echo esc_attr( $meta['custom_border_color'] ); ?>; color: <?php echo esc_attr( $meta['custom_text_color'] ); ?>;">
									<div id="fed_ntf_preview_icon_badge"
										 class="w-8 h-8 rounded-xl flex items-center justify-center text-sm shrink-0 mt-0.5"
										 style="background-color: rgba(255,255,255,0.18); color: <?php echo esc_attr( $meta['custom_text_color'] ); ?>; border: 1px solid <?php echo esc_attr( $meta['custom_border_color'] ); ?>;">
										<i id="fed_ntf_preview_icon" class="<?php echo esc_attr( $meta['custom_icon'] ); ?>" style="color: <?php echo esc_attr( $meta['custom_text_color'] ); ?> !important;"></i>
									</div>
									<div class="min-w-0 space-y-1 flex-1">
										<div id="fed_ntf_preview_title" class="text-xs font-bold leading-snug" style="color: <?php echo esc_attr( $meta['custom_text_color'] ); ?> !important;">
											<?php echo esc_html( ! empty( $title ) ? $title : __( 'Sample Notification Headline', 'frontend-dashboard-notification' ) ); ?>
										</div>
										<div id="fed_ntf_preview_desc" class="text-[11px] leading-relaxed opacity-90" style="color: <?php echo esc_attr( $meta['custom_text_color'] ); ?> !important;">
											<?php esc_html_e( 'This is a live preview showing how your custom colors and theme match your dashboard.', 'frontend-dashboard-notification' ); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Card 3: Behavior & Dismissal Settings -->
					<div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-xs space-y-5">
						<div class="pb-3 border-b border-slate-100 flex items-center gap-2.5">
							<div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
								<i class="fas fa-sliders-h"></i>
							</div>
							<div>
								<h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 m-0"><?php esc_html_e( 'Display & Dismissal Rules', 'frontend-dashboard-notification' ); ?></h4>
								<p class="text-[11px] text-slate-400 m-0"><?php esc_html_e( 'Control publication status and dismiss button behavior.', 'frontend-dashboard-notification' ); ?></p>
							</div>
						</div>

						<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
							<!-- Status -->
							<div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-2">
								<label for="fed_notification_status" class="block text-xs font-bold text-slate-800">
									<?php esc_html_e( 'Publication Status', 'frontend-dashboard-notification' ); ?>
								</label>
								<select name="fed_notification_status"
										id="fed_notification_status"
										class="w-full px-3.5 py-2 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-indigo-500 transition-all cursor-pointer">
									<option value="Enable" <?php selected( $meta['status'], 'Enable' ); ?>><?php esc_html_e( 'Enable (Active)', 'frontend-dashboard-notification' ); ?></option>
									<option value="Disable" <?php selected( $meta['status'], 'Disable' ); ?>><?php esc_html_e( 'Disable (Paused)', 'frontend-dashboard-notification' ); ?></option>
								</select>
								<p class="text-[11px] text-slate-400 m-0"><?php esc_html_e( 'Pause or activate this notification.', 'frontend-dashboard-notification' ); ?></p>
							</div>

							<!-- Close Button -->
							<div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-2">
								<label for="fed_notification_close_button" class="block text-xs font-bold text-slate-800">
									<?php esc_html_e( 'Dismiss / Close Button', 'frontend-dashboard-notification' ); ?>
								</label>
								<select name="fed_notification[close_button]"
										id="fed_notification_close_button"
										class="w-full px-3.5 py-2 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-indigo-500 transition-all cursor-pointer">
									<option value="Enable" <?php selected( $meta['close_button'], 'Enable' ); ?>><?php esc_html_e( 'Enable (Allow users to dismiss)', 'frontend-dashboard-notification' ); ?></option>
									<option value="Disable" <?php selected( $meta['close_button'], 'Disable' ); ?>><?php esc_html_e( 'Disable (Persistent notice)', 'frontend-dashboard-notification' ); ?></option>
								</select>
								<p class="text-[11px] text-slate-400 m-0"><?php esc_html_e( 'Show (x) button on the alert card.', 'frontend-dashboard-notification' ); ?></p>
							</div>

							<!-- Close Action -->
							<div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-2">
								<label for="fed_notification_close_action" class="block text-xs font-bold text-slate-800">
									<?php esc_html_e( 'Dismiss Persistence', 'frontend-dashboard-notification' ); ?>
								</label>
								<select name="fed_notification[close_action]"
										id="fed_notification_close_action"
										class="w-full px-3.5 py-2 text-xs text-slate-800 bg-white border border-slate-200 rounded-xl focus:border-indigo-500 transition-all cursor-pointer">
									<option value="close_permanently" <?php selected( $meta['close_action'], 'close_permanently' ); ?>><?php esc_html_e( 'Close Permanently (Never show again)', 'frontend-dashboard-notification' ); ?></option>
									<option value="close_one_time" <?php selected( $meta['close_action'], 'close_one_time' ); ?>><?php esc_html_e( 'Close One-Time (Hide until refresh)', 'frontend-dashboard-notification' ); ?></option>
								</select>
								<p class="text-[11px] text-slate-400 m-0"><?php esc_html_e( 'Action taken when user clicks close.', 'frontend-dashboard-notification' ); ?></p>
							</div>
						</div>
					</div>

					<!-- Card 4: Target Locations & Slots -->
					<div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-xs space-y-5">
						<div class="pb-3 border-b border-slate-100 flex items-center justify-between">
							<div class="flex items-center gap-2.5">
								<div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
									<i class="fas fa-map-marker-alt"></i>
								</div>
								<div>
									<h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 m-0"><?php esc_html_e( 'Display Positions / Slots', 'frontend-dashboard-notification' ); ?></h4>
									<p class="text-[11px] text-slate-400 m-0"><?php esc_html_e( 'Select where in the Frontend Dashboard layout this notification should render.', 'frontend-dashboard-notification' ); ?></p>
								</div>
							</div>
							<button type="button" class="fed-ntf-select-all-btn text-[11px] font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-lg transition-colors cursor-pointer border-0" data-target="location">
								<?php esc_html_e( 'Toggle All Slots', 'frontend-dashboard-notification' ); ?>
							</button>
						</div>

						<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
							<?php
							foreach ( $locations as $loc_key => $loc_name ) :
								$checked = ( is_array( $meta['locations'] ) && in_array( $loc_key, $meta['locations'], true ) );
								?>
								<label class="p-3.5 bg-slate-50/80 hover:bg-slate-100/70 border border-slate-200/80 rounded-2xl flex items-center justify-between gap-3 cursor-pointer transition-all">
									<div class="flex items-center gap-2.5 min-w-0">
										<i class="fas fa-th-large text-slate-400 text-xs shrink-0"></i>
										<span class="text-xs font-bold text-slate-800 truncate select-none"><?php echo esc_html( $loc_name ); ?></span>
									</div>
									<input type="checkbox"
										   name="fed_notification[locations][]"
										   value="<?php echo esc_attr( $loc_key ); ?>"
										   <?php checked( $checked, true ); ?>
										   class="fed-ntf-location-cb w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer" />
								</label>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Card 5: Target Dashboard Menus -->
					<div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-xs space-y-5">
						<div class="pb-3 border-b border-slate-100 flex items-center justify-between">
							<div class="flex items-center gap-2.5">
								<div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
									<i class="fas fa-bars"></i>
								</div>
								<div>
									<h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 m-0"><?php esc_html_e( 'Target Dashboard Menus', 'frontend-dashboard-notification' ); ?></h4>
									<p class="text-[11px] text-slate-400 m-0"><?php esc_html_e( 'Choose which menu pages will display this notification (or choose All Menus).', 'frontend-dashboard-notification' ); ?></p>
								</div>
							</div>
							<button type="button" class="fed-ntf-select-all-btn text-[11px] font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-lg transition-colors cursor-pointer border-0" data-target="menu">
								<?php esc_html_e( 'Toggle All Menus', 'frontend-dashboard-notification' ); ?>
							</button>
						</div>

						<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
							<!-- All Menus Option -->
							<?php
							$all_menus_checked = ( is_array( $meta['menus'] ) && in_array( 'all', $meta['menus'], true ) );
							?>
							<label class="p-3.5 bg-indigo-50/60 hover:bg-indigo-50 border border-indigo-200/80 rounded-2xl flex items-center justify-between gap-3 cursor-pointer transition-all sm:col-span-2 lg:col-span-3">
								<div class="flex items-center gap-2.5">
									<i class="fas fa-globe text-indigo-600 text-xs"></i>
									<div>
										<span class="text-xs font-bold text-indigo-950 select-none"><?php esc_html_e( 'All Dashboard Menus (Universal Broadcast)', 'frontend-dashboard-notification' ); ?></span>
										<p class="text-[11px] text-indigo-700/80 m-0 select-none"><?php esc_html_e( 'Show across every Frontend Dashboard menu tab.', 'frontend-dashboard-notification' ); ?></p>
									</div>
								</div>
								<input type="checkbox"
									   name="fed_notification[menus][]"
									   value="all"
									   id="fed_ntf_all_menus_cb"
									   <?php checked( $all_menus_checked, true ); ?>
									   class="w-4 h-4 text-indigo-600 rounded border-indigo-300 focus:ring-indigo-500 cursor-pointer" />
							</label>

							<?php
							foreach ( $menu_items as $menu_slug => $menu_name ) :
								$checked = ( ! $all_menus_checked && is_array( $meta['menus'] ) && in_array( $menu_slug, $meta['menus'], true ) );
								?>
								<label class="p-3.5 bg-slate-50/80 hover:bg-slate-100/70 border border-slate-200/80 rounded-2xl flex items-center justify-between gap-3 cursor-pointer transition-all fed-ntf-specific-menu-item">
									<div class="flex items-center gap-2.5 min-w-0">
										<i class="fas fa-folder text-slate-400 text-xs shrink-0"></i>
										<span class="text-xs font-bold text-slate-800 truncate select-none"><?php echo esc_html( $menu_name ); ?></span>
									</div>
									<input type="checkbox"
										   name="fed_notification[menus][]"
										   value="<?php echo esc_attr( $menu_slug ); ?>"
										   <?php checked( $checked, true ); ?>
										   class="fed-ntf-menu-cb w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer" />
								</label>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Card 6: Target User Roles -->
					<div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-xs space-y-4">
						<?php
						$current_roles = is_array( $meta['user_roles'] ) ? $meta['user_roles'] : array( 'all' );
						if ( in_array( 'all', $current_roles, true ) ) {
							$current_roles = array_keys( $all_roles );
						}

						fed_render_user_roles_selector(
							array(
								'name_prefix'         => 'fed_notification[user_roles]',
								'selected'            => $current_roles,
								'all_roles'           => $all_roles,
								'default_all_checked' => true,
								'show_mode_switch'    => true,
								'title'               => __( 'Target User Roles', 'frontend-dashboard-notification' ),
								'description'         => __( 'Select the user roles permitted to view this notification in their dashboard.', 'frontend-dashboard-notification' ),
							)
						);
						?>
					</div>

					<!-- Submit Actions -->
					<div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
						<a href="<?php echo esc_url( $list_url ); ?>"
						   class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all no-underline cursor-pointer">
							<?php esc_html_e( 'Cancel', 'frontend-dashboard-notification' ); ?>
						</a>
						<button type="submit"
								class="fed-btn-primary h-11 inline-flex items-center justify-center gap-2 px-7 rounded-xl font-semibold text-xs tracking-wide shadow-sm transition-all active:scale-95 cursor-pointer border-0"
								style="background-color: #4f46e5 !important; color: #ffffff !important;">
							<i class="fas fa-save text-xs" style="color: #ffffff !important;"></i>
							<span style="color: #ffffff !important;"><?php echo $is_edit ? esc_html__( 'Update Notification', 'frontend-dashboard-notification' ) : esc_html__( 'Save & Publish Notification', 'frontend-dashboard-notification' ); ?></span>
						</button>
					</div>
				</form>
			</div>

			<script>
			(function($) {
				'use strict';
				$(document).ready(function() {
					// Show/hide custom color panel
					function updateCustomColorPanel() {
						var selected = $('input[name="fed_notification[style_type]"]:checked').val();
						if (selected === 'custom') {
							$('#fed_ntf_custom_color_panel').removeClass('hidden').slideDown(150);
						} else {
							$('#fed_ntf_custom_color_panel').slideUp(150);
						}
					}

					// Style card selection styling
					$(document).on('change', '.fed-ntf-style-radio', function() {
						$('.fed-ntf-style-card').removeClass('bg-indigo-50/80 border-indigo-300 ring-2 ring-indigo-500/20 shadow-xs')
						                        .addClass('bg-slate-50/70 border-slate-200/80');
						var $activeCard = $(this).closest('.fed-ntf-style-card');
						$activeCard.addClass('bg-indigo-50/80 border-indigo-300 ring-2 ring-indigo-500/20 shadow-xs')
						           .removeClass('bg-slate-50/70 border-slate-200/80');
						updateCustomColorPanel();
					});

					// Live Color Sync
					function updateLivePreview() {
						var bg = $('#fed_ntf_input_bg').val() || '#e0e7ff';
						var text = $('#fed_ntf_input_text').val() || '#1e1b4b';
						var border = $('#fed_ntf_input_border').val() || '#818cf8';
						var icon = $('#fed_ntf_select_icon').val() || 'fas fa-bell';
						var title = $('#fed_ntf_title').val() || 'Sample Notification Headline';

						$('#fed_ntf_live_preview').css({
							'background-color': bg,
							'border-color': border,
							'color': text
						});
						$('#fed_ntf_preview_icon_badge').css({
							'color': text,
							'border-color': border,
							'background-color': 'rgba(255,255,255,0.18)'
						});
						$('#fed_ntf_preview_icon').attr('class', icon).css('color', text);
						$('#fed_ntf_preview_title').text(title).css('color', text);
						$('#fed_ntf_preview_desc').css('color', text);
					}

					updateLivePreview();

					$('#fed_ntf_picker_bg').on('input change', function() {
						$('#fed_ntf_input_bg').val($(this).val());
						updateLivePreview();
					});
					$('#fed_ntf_input_bg').on('input change', function() {
						var val = $(this).val();
						if (/^#[0-9A-F]{6}$/i.test(val)) {
							$('#fed_ntf_picker_bg').val(val);
						}
						updateLivePreview();
					});

					$('#fed_ntf_picker_text').on('input change', function() {
						$('#fed_ntf_input_text').val($(this).val());
						updateLivePreview();
					});
					$('#fed_ntf_input_text').on('input change', function() {
						var val = $(this).val();
						if (/^#[0-9A-F]{6}$/i.test(val)) {
							$('#fed_ntf_picker_text').val(val);
						}
						updateLivePreview();
					});

					$('#fed_ntf_picker_border').on('input change', function() {
						$('#fed_ntf_input_border').val($(this).val());
						updateLivePreview();
					});
					$('#fed_ntf_input_border').on('input change', function() {
						var val = $(this).val();
						if (/^#[0-9A-F]{6}$/i.test(val)) {
							$('#fed_ntf_picker_border').val(val);
						}
						updateLivePreview();
					});

					$('#fed_ntf_select_icon').on('change', function() {
						updateLivePreview();
					});
					$('#fed_ntf_title').on('input', function() {
						updateLivePreview();
					});

					// "All Menus" toggle behavior
					$(document).on('change', '#fed_ntf_all_menus_cb', function() {
						if ($(this).is(':checked')) {
							$('.fed-ntf-menu-cb').prop('checked', false);
						}
					});

					$(document).on('change', '.fed-ntf-menu-cb', function() {
						if ($(this).is(':checked')) {
							$('#fed_ntf_all_menus_cb').prop('checked', false);
						}
					});

					// Select all slots toggle button
					$(document).on('click', '.fed-ntf-select-all-btn', function() {
						var target = $(this).data('target');
						if (target === 'location') {
							var $cbs = $('.fed-ntf-location-cb');
							var allChecked = ($cbs.filter(':checked').length === $cbs.length);
							$cbs.prop('checked', !allChecked);
						} else if (target === 'menu') {
							var $cbs = $('.fed-ntf-menu-cb');
							var allChecked = ($cbs.filter(':checked').length === $cbs.length);
							$cbs.prop('checked', !allChecked);
							if (!allChecked) {
								$('#fed_ntf_all_menus_cb').prop('checked', false);
							}
						}
					});
				});
			})(jQuery);
			</script>
			<?php
		}

		/**
		 * AJAX: Save or Update Notification.
		 */
		public function ajax_save_notification() {
			$post_data = wp_unslash( $_POST );
			fed_verify_nonce( $post_data );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to manage notifications.', 'frontend-dashboard-notification' ) ) );
			}

			$title   = isset( $post_data['fed_notification_title'] ) ? sanitize_text_field( $post_data['fed_notification_title'] ) : '';
			$content = isset( $post_data['fed_notification_content'] ) ? wp_kses_post( $post_data['fed_notification_content'] ) : '';
			$ntf_id  = isset( $post_data['notification_id'] ) ? absint( $post_data['notification_id'] ) : 0;
			$status  = isset( $post_data['fed_notification_status'] ) ? sanitize_text_field( $post_data['fed_notification_status'] ) : 'Enable';

			if ( empty( $title ) ) {
				wp_send_json_error( array( 'message' => __( 'Please provide a notification title.', 'frontend-dashboard-notification' ) ) );
			}

			if ( empty( $content ) ) {
				wp_send_json_error( array( 'message' => __( 'Please provide notification message content.', 'frontend-dashboard-notification' ) ) );
			}

			$payload_ntf = isset( $post_data['fed_notification'] ) && is_array( $post_data['fed_notification'] ) ? $post_data['fed_notification'] : array();

			// Sanitize locations
			$locations = array();
			if ( isset( $payload_ntf['locations'] ) && is_array( $payload_ntf['locations'] ) ) {
				foreach ( $payload_ntf['locations'] as $loc ) {
					$locations[] = sanitize_text_field( $loc );
				}
			}
			if ( empty( $locations ) ) {
				$locations = array( 'content_top' );
			}

			// Sanitize menus
			$menus = array();
			if ( isset( $payload_ntf['menus'] ) && is_array( $payload_ntf['menus'] ) ) {
				foreach ( $payload_ntf['menus'] as $m ) {
					$menus[] = sanitize_text_field( $m );
				}
			}
			if ( empty( $menus ) ) {
				$menus = array( 'all' );
			}

			// Sanitize roles
			$user_roles = array();
			if ( isset( $payload_ntf['user_roles'] ) && is_array( $payload_ntf['user_roles'] ) ) {
				foreach ( $payload_ntf['user_roles'] as $k => $v ) {
					if ( is_numeric( $k ) ) {
						$user_roles[] = sanitize_text_field( $v );
					} elseif ( 'Enable' === $v || true === $v || 1 === $v || '1' === $v ) {
						$user_roles[] = sanitize_text_field( $k );
					}
				}
			}
			if ( empty( $user_roles ) ) {
				$user_roles = array( 'all' );
			}

			$close_button        = isset( $payload_ntf['close_button'] ) ? sanitize_text_field( $payload_ntf['close_button'] ) : 'Enable';
			$close_action        = isset( $payload_ntf['close_action'] ) ? sanitize_text_field( $payload_ntf['close_action'] ) : 'close_permanently';
			$style_type          = isset( $payload_ntf['style_type'] ) ? sanitize_text_field( $payload_ntf['style_type'] ) : 'neutral';
			$custom_bg_color     = isset( $payload_ntf['custom_bg_color'] ) ? sanitize_hex_color( $payload_ntf['custom_bg_color'] ) : '#e0e7ff';
			$custom_text_color   = isset( $payload_ntf['custom_text_color'] ) ? sanitize_hex_color( $payload_ntf['custom_text_color'] ) : '#1e1b4b';
			$custom_border_color = isset( $payload_ntf['custom_border_color'] ) ? sanitize_hex_color( $payload_ntf['custom_border_color'] ) : '#818cf8';
			$custom_icon         = isset( $payload_ntf['custom_icon'] ) ? sanitize_text_field( $payload_ntf['custom_icon'] ) : 'fas fa-bell';

			if ( empty( $custom_bg_color ) ) {
				$custom_bg_color = '#e0e7ff';
			}
			if ( empty( $custom_text_color ) ) {
				$custom_text_color = '#1e1b4b';
			}
			if ( empty( $custom_border_color ) ) {
				$custom_border_color = '#818cf8';
			}

			$post_args = array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_type'    => 'fed-notification',
				'post_status'  => 'publish',
			);

			if ( $ntf_id > 0 ) {
				$post_args['ID'] = $ntf_id;
				$result_id       = wp_update_post( $post_args );
			} else {
				$result_id = wp_insert_post( $post_args );
			}

			if ( is_wp_error( $result_id ) || ! $result_id ) {
				wp_send_json_error( array( 'message' => __( 'Failed to save notification. Please try again.', 'frontend-dashboard-notification' ) ) );
			}

			// Save Meta
			$meta_data = array(
				'locations'           => $locations,
				'menus'               => $menus,
				'user_roles'          => $user_roles,
				'close_button'        => $close_button,
				'close_action'        => $close_action,
				'style_type'          => $style_type,
				'custom_bg_color'     => $custom_bg_color,
				'custom_text_color'   => $custom_text_color,
				'custom_border_color' => $custom_border_color,
				'custom_icon'         => $custom_icon,
			);

			update_post_meta( $result_id, 'fed_ntf_notification', $meta_data );
			update_post_meta( $result_id, 'fed_ntf_notification_status', $status );

			$current_page = isset( $post_data['current_page'] ) ? sanitize_text_field( $post_data['current_page'] ) : 'fed_notification';
			$redirect_url = ( 'fed_settings' === $current_page ) ? admin_url( 'admin.php?page=fed_settings#notification' ) : admin_url( 'admin.php?page=fed_notification' );

			wp_send_json_success(
				array(
					'message'  => __( 'Notification saved successfully!', 'frontend-dashboard-notification' ),
					'reload'   => $redirect_url,
					'redirect' => $redirect_url,
				)
			);
		}

		/**
		 * AJAX: Delete Notification.
		 */
		public function ajax_delete_notification() {
			$post_data = wp_unslash( $_POST );
			fed_verify_nonce( $post_data );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to delete notifications.', 'frontend-dashboard-notification' ) ) );
			}

			$id = isset( $post_data['id'] ) ? absint( $post_data['id'] ) : 0;
			if ( ! $id ) {
				wp_send_json_error( array( 'message' => __( 'Invalid notification ID.', 'frontend-dashboard-notification' ) ) );
			}

			$deleted = wp_delete_post( $id, true );
			if ( $deleted ) {
				wp_send_json_success( array( 'message' => __( 'Notification deleted successfully.', 'frontend-dashboard-notification' ) ) );
			} else {
				wp_send_json_error( array( 'message' => __( 'Failed to delete notification.', 'frontend-dashboard-notification' ) ) );
			}
		}

		/**
		 * AJAX: Quick Toggle Notification Status.
		 */
		public function ajax_toggle_status() {
			$post_data = wp_unslash( $_POST );
			fed_verify_nonce( $post_data );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to modify status.', 'frontend-dashboard-notification' ) ) );
			}

			$id = isset( $post_data['id'] ) ? absint( $post_data['id'] ) : 0;
			if ( ! $id ) {
				wp_send_json_error( array( 'message' => __( 'Invalid notification ID.', 'frontend-dashboard-notification' ) ) );
			}

			$current_status = get_post_meta( $id, 'fed_ntf_notification_status', true );
			$new_status     = ( 'Enable' === $current_status || empty( $current_status ) ) ? 'Disable' : 'Enable';

			update_post_meta( $id, 'fed_ntf_notification_status', $new_status );

			wp_send_json_success(
				array(
					'status'  => $new_status,
					'message' => sprintf(
						/* translators: %s: New Status */
						__( 'Notification is now %s.', 'frontend-dashboard-notification' ),
						$new_status
					),
				)
			);
		}
	}

	new FED_NTF_Admin_Settings();
}
