<?php
/**
 * Default Notification Functions.
 *
 * @package frontend-dashboard-notification
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notification Locations.
 *
 * @return array
 */
function fed_ntf_notification_locations() {
	return apply_filters(
		'fed_ntf_notification_locations',
		array(
			'content_outside_top'    => __( 'Content Outside Top', 'frontend-dashboard-notification' ),
			'content_top'            => __( 'Content Top', 'frontend-dashboard-notification' ),
			'panel_inside_top'       => __( 'Panel Inside Top', 'frontend-dashboard-notification' ),
			'panel_inside_bottom'    => __( 'Panel Inside Bottom', 'frontend-dashboard-notification' ),
			'content_bottom'         => __( 'Content Bottom', 'frontend-dashboard-notification' ),
			'content_outside_bottom' => __( 'Content Outside Bottom', 'frontend-dashboard-notification' ),
		)
	);
}

/**
 * Notification Styles / Alert Types.
 *
 * @return array
 */
function fed_ntf_notification_styles() {
	return apply_filters(
		'fed_ntf_notification_styles',
		array(
			'info'    => array(
				'label'      => __( 'Info (Blue)', 'frontend-dashboard-notification' ),
				'icon'       => 'fas fa-info-circle',
				'bg'         => 'bg-sky-50/80',
				'border'     => 'border-sky-200',
				'text'       => 'text-sky-900',
				'icon_color' => 'text-sky-600',
				'badge'      => 'bg-sky-100 text-sky-800',
			),
			'success' => array(
				'label'      => __( 'Success (Green)', 'frontend-dashboard-notification' ),
				'icon'       => 'fas fa-check-circle',
				'bg'         => 'bg-emerald-50/80',
				'border'     => 'border-emerald-200',
				'text'       => 'text-emerald-900',
				'icon_color' => 'text-emerald-600',
				'badge'      => 'bg-emerald-100 text-emerald-800',
			),
			'warning' => array(
				'label'      => __( 'Warning (Amber)', 'frontend-dashboard-notification' ),
				'icon'       => 'fas fa-exclamation-triangle',
				'bg'         => 'bg-amber-50/80',
				'border'     => 'border-amber-200',
				'text'       => 'text-amber-900',
				'icon_color' => 'text-amber-600',
				'badge'      => 'bg-amber-100 text-amber-800',
			),
			'danger'  => array(
				'label'      => __( 'Danger (Red)', 'frontend-dashboard-notification' ),
				'icon'       => 'fas fa-times-circle',
				'bg'         => 'bg-rose-50/80',
				'border'     => 'border-rose-200',
				'text'       => 'text-rose-900',
				'icon_color' => 'text-rose-600',
				'badge'      => 'bg-rose-100 text-rose-800',
			),
			'neutral' => array(
				'label'      => __( 'Brand / Neutral (Indigo)', 'frontend-dashboard-notification' ),
				'icon'       => 'fas fa-bullhorn',
				'bg'         => 'bg-indigo-50/80',
				'border'     => 'border-indigo-200',
				'text'       => 'text-indigo-950',
				'icon_color' => 'text-indigo-600',
				'badge'      => 'bg-indigo-100 text-indigo-800',
			),
			'custom'  => array(
				'label'      => __( 'Custom Theme Color', 'frontend-dashboard-notification' ),
				'icon'       => 'fas fa-palette',
				'bg'         => 'bg-slate-50',
				'border'     => 'border-slate-300',
				'text'       => 'text-slate-900',
				'icon_color' => 'text-slate-700',
				'badge'      => 'bg-purple-100 text-purple-800',
			),
		)
	);
}

/**
 * Get Notification Metadata.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function fed_ntf_get_notification_meta( $post_id ) {
	$meta = get_post_meta( $post_id, 'fed_ntf_notification', true );
	if ( ! is_array( $meta ) ) {
		$meta = array();
	}

	$status = get_post_meta( $post_id, 'fed_ntf_notification_status', true );
	if ( empty( $status ) ) {
		$status = 'Enable';
	}

	return array(
		'status'              => $status,
		'locations'           => isset( $meta['locations'] ) && is_array( $meta['locations'] ) ? $meta['locations'] : array( 'content_top' ),
		'menus'               => isset( $meta['menus'] ) && is_array( $meta['menus'] ) ? $meta['menus'] : array( 'all' ),
		'user_roles'          => isset( $meta['user_roles'] ) && is_array( $meta['user_roles'] ) ? $meta['user_roles'] : array( 'all' ),
		'close_button'        => isset( $meta['close_button'] ) ? $meta['close_button'] : 'Enable',
		'close_action'        => isset( $meta['close_action'] ) ? $meta['close_action'] : 'close_permanently',
		'style_type'          => isset( $meta['style_type'] ) ? $meta['style_type'] : 'neutral',
		'custom_bg_color'     => isset( $meta['custom_bg_color'] ) && ! empty( $meta['custom_bg_color'] ) ? $meta['custom_bg_color'] : '#f8fafc',
		'custom_text_color'   => isset( $meta['custom_text_color'] ) && ! empty( $meta['custom_text_color'] ) ? $meta['custom_text_color'] : '#0f172a',
		'custom_border_color' => isset( $meta['custom_border_color'] ) && ! empty( $meta['custom_border_color'] ) ? $meta['custom_border_color'] : '#cbd5e1',
		'custom_icon'         => isset( $meta['custom_icon'] ) && ! empty( $meta['custom_icon'] ) ? $meta['custom_icon'] : 'fas fa-bell',
	);
}

/**
 * Get all notifications for management.
 *
 * @param int $paged Page number.
 * @param int $per_page Posts per page.
 * @return WP_Query
 */
function fed_ntf_get_notifications_query( $paged = 1, $per_page = 10 ) {
	$args = array(
		'post_type'        => 'fed-notification',
		'post_status'      => 'any',
		'posts_per_page'   => $per_page,
		'paged'            => $paged,
		'orderby'          => 'date',
		'order'            => 'DESC',
	);

	return new WP_Query( $args );
}

/**
 * Get all notifications (unpaginated array for counts / simple iteration).
 *
 * @return WP_Post[]
 */
function fed_ntf_get_all_notifications() {
	return get_posts(
		array(
			'post_type'        => 'fed-notification',
			'post_status'      => 'any',
			'numberposts'      => -1,
			'orderby'          => 'date',
			'order'            => 'DESC',
		)
	);
}

