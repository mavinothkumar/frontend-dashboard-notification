<?php
/**
 * Enqueue Scripts and Style.
 *
 * @package frontend-dashboard-notification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'fed_default_admin_scripts_styles', 'fed_ntf_default_scripts_styles' );
add_filter( 'fed_default_frontend_scripts_styles', 'fed_ntf_default_scripts_styles' );
add_filter( 'fed_admin_script_loading_pages', 'fed_ntf_admin_script_loading_pages' );

/**
 * Register Admin and Frontend Scripts & Styles.
 *
 * @param array $scripts Scripts array.
 * @return array
 */
function fed_ntf_default_scripts_styles( $scripts ) {
	$scripts['styles']['fed_ntf_style'] = array(
		'wp_core'      => false,
		'name'         => 'FED NTF Style',
		'plugin_name'  => 'Frontend Dashboard Notification',
		'src'          => plugins_url( '/assets/css/fed_ntf_style.css', BC_FED_NTF_PLUGIN ),
		'dependencies' => array(),
		'version'      => BC_FED_NTF_PLUGIN_VERSION,
		'media'        => 'all',
	);

	$scripts['scripts']['fed_ntf_script'] = array(
		'wp_core'      => false,
		'name'         => 'FED NTF Script',
		'plugin_name'  => 'Frontend Dashboard Notification',
		'src'          => plugins_url( '/assets/js/fed_ntf_script.js', BC_FED_NTF_PLUGIN ),
		'dependencies' => array( 'jquery' ),
		'version'      => BC_FED_NTF_PLUGIN_VERSION,
		'in_footer'    => true,
	);

	return $scripts;
}

/**
 * Script Loading Pages.
 *
 * @param array $page_slug Page Slugs.
 * @return array
 */
function fed_ntf_admin_script_loading_pages( $page_slug ) {
	$page_slug[] = 'fed_settings';
	$page_slug[] = 'fed_notification';
	$page_slug[] = 'fed-notification';
	return $page_slug;
}

/**
 * Enqueue WordPress media and editor dependencies on admin notification pages.
 *
 * @param string $hook Admin page hook.
 */
function fed_ntf_admin_enqueue_media( $hook ) {
	if ( isset( $_GET['page'] ) && in_array( sanitize_text_field( wp_unslash( $_GET['page'] ) ), array( 'fed_notification', 'fed_settings' ), true ) ) {
		wp_enqueue_media();
		wp_enqueue_editor();
	}
}
add_action( 'admin_enqueue_scripts', 'fed_ntf_admin_enqueue_media' );

