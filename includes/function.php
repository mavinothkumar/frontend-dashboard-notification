<?php
/**
 * Default Notification Functions.
 *
 * @package frontend-dashboard-notification
 */


/**
 * Notification Locations.
 *
 * @return array
 */
function fed_ntf_notification_locations() {
	return apply_filters( 'fed_ntf_notification_locations', array(
		'content_outside_top'    => __( 'Content Outside Top', 'frontend-dashboard-notification' ),
		'content_top'            => __( 'Content Top', 'frontend-dashboard-notification' ),
		'content_bottom'         => __( 'Content Bottom', 'frontend-dashboard-notification' ),
		'content_outside_bottom' => __( 'Content Outside Bottom', 'frontend-dashboard-notification' ),
	) );
}
