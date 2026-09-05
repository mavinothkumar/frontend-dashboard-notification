<?php
/**
 * Enqueue Scripts and Style.
 *
 * @package frontend-dashboard-notification.
 */

add_filter( 'fed_default_admin_scripts_styles', 'fed_ntf_default_scripts_styles' );
add_filter( 'fed_default_frontend_scripts_styles', 'fed_ntf_default_scripts_styles' );
add_filter( 'fed_admin_script_loading_pages', 'fed_ntf_admin_script_loading_pages' );
/**
 * Admin Scripts.
 *
 * @param  array $scripts  Scripts.
 *
 * @return mixed
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
		'dependencies' => array(),
		'version'      => BC_FED_NTF_PLUGIN_VERSION ,
		'in_footer'    => true,
	);

	$scripts['styles']['wp-block'] = array(
		'wp_core'     => true,
		'name'        => 'WP Blocks',
		'plugin_name' => 'Frontend Dashboard Notification',
	);

	return $scripts;
}


/**
 * Script Loading Pages.
 *
 * @param  array $page_slug  Page Slug.
 *
 * @return mixed
 */
function fed_ntf_admin_script_loading_pages( $page_slug ) {
	$page_slug[] = 'fed-notification';

	return $page_slug;
}
