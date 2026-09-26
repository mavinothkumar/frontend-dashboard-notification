<?php
/**
 * Custom Post Type for Notifications.
 *
 * @package Frontend Dashboard Notification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FED_NTF_Custom_Post' ) ) {
	/**
	 * Class FED_NTF_Custom_Post
	 */
	class FED_NTF_Custom_Post {
		/**
		 * FED_NTF_Custom_Post constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'create' ) );
		}

		/**
		 * Create Custom post.
		 */
		public function create() {
			$labels = array(
				'name'               => __( 'Notifications', 'frontend-dashboard-notification' ),
				'singular_name'      => __( 'Notification', 'frontend-dashboard-notification' ),
				'menu_name'          => __( 'Notifications', 'frontend-dashboard-notification' ),
				'name_admin_bar'     => __( 'Notification', 'frontend-dashboard-notification' ),
				'add_new'            => __( 'Add New', 'frontend-dashboard-notification' ),
				'add_new_item'       => __( 'Add New Notification', 'frontend-dashboard-notification' ),
				'new_item'           => __( 'New Notification', 'frontend-dashboard-notification' ),
				'edit_item'          => __( 'Edit Notification', 'frontend-dashboard-notification' ),
				'view_item'          => __( 'View Notification', 'frontend-dashboard-notification' ),
				'all_items'          => __( 'All Notifications', 'frontend-dashboard-notification' ),
				'search_items'       => __( 'Search Notifications', 'frontend-dashboard-notification' ),
				'not_found'          => __( 'No Notifications found.', 'frontend-dashboard-notification' ),
				'not_found_in_trash' => __( 'No Notifications found in Trash.', 'frontend-dashboard-notification' ),
			);

			$options = array(
				'labels'              => $labels,
				'description'         => __( 'Frontend Dashboard Notification storage post type', 'frontend-dashboard-notification' ),
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => false,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => false,
				'query_var'           => false,
				'rewrite'             => false,
				'capability_type'     => 'post',
				'has_archive'         => false,
				'hierarchical'        => false,
				'supports'            => array( 'title', 'editor' ),
				'exclude_from_search' => true,
			);

			register_post_type( 'fed-notification', $options );
		}
	}

	new FED_NTF_Custom_Post();
}
