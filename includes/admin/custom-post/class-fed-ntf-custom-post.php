<?php
/**
 * Main Menu.
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
				'name'                  => __( 'Notifications', 'frontend-dashboard-notification' ),
				'singular_name'         => __( 'Notification', 'frontend-dashboard-notification' ),
				'menu_name'             => __( 'Notifications', 'frontend-dashboard-notification' ),
				'name_admin_bar'        => __( 'Notification', 'frontend-dashboard-notification' ),
				'add_new'               => __( 'Add New', 'frontend-dashboard-notification' ),
				'add_new_item'          => __( 'Add New Notification', 'frontend-dashboard-notification' ),
				'new_item'              => __( 'New Notification', 'frontend-dashboard-notification' ),
				'edit_item'             => __( 'Edit Notification', 'frontend-dashboard-notification' ),
				'view_item'             => __( 'View Notification', 'frontend-dashboard-notification' ),
				'all_items'             => __( 'All Notifications', 'frontend-dashboard-notification' ),
				'search_items'          => __( 'Search Notifications', 'frontend-dashboard-notification' ),
				'parent_item_colon'     => __( 'Parent Notifications:', 'frontend-dashboard-notification' ),
				'not_found'             => __( 'No Notifications found.', 'frontend-dashboard-notification' ),
				'not_found_in_trash'    => __( 'No Notifications found in Trash.', 'frontend-dashboard-notification' ),
				'featured_image'        => __( 'Notification Cover Image', 'frontend-dashboard-notification' ),
				'set_featured_image'    => __( 'Set cover image', 'frontend-dashboard-notification' ),
				'remove_featured_image' => __( 'Remove cover image', 'frontend-dashboard-notification' ),
				'use_featured_image'    => __( 'Use as cover image', 'frontend-dashboard-notification' ),
				'archives'              => __( 'Notification archives', 'frontend-dashboard-notification' ),
				'insert_into_item'      => __( 'Insert into Notification', 'frontend-dashboard-notification' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Notification', 'frontend-dashboard-notification' ),
				'filter_items_list'     => __( 'Filter Notifications list', 'frontend-dashboard-notification' ),
				'items_list_navigation' => __( 'Notifications list navigation', 'frontend-dashboard-notification' ),
				'items_list'            => __( 'Notifications list', 'frontend-dashboard-notification' ),
			);

			$options = array(
				'labels'             => $labels,
				'description'        => __( 'Frontend Dashboard Notification is an add-on of Frontend Dashboard WordPress Plugin',
					'frontend-dashboard-notification' ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'show_in_admin_bar'  => true,
				'rewrite'            => array( 'slug' => 'fed_notification' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'show_in_nav_menus'  => true,
				'menu_position'      => 3,
				'supports'           => array( 'title', 'editor' ),
				'show_in_rest'       => true,
				'menu_icon'          => 'dashicons-warning',
			);

			register_post_type( 'fed-notification', $options );
		}
	}

	new FED_NTF_Custom_Post();
}