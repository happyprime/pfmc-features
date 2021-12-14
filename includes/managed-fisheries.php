<?php
/**
 * Handling for the Managed Fishery post type.
 *
 * @package PFMC_Features
 */

namespace PFMCFS\Post_Type\Managed_Fisheries;

add_action( 'init', __NAMESPACE__ . '\register_post_type', 10 );

/**
 * Register the Managed Fishery post type.
 */
function register_post_type() {

	$labels = array(
		'name'          => _x( 'Managed Fisheries', 'Post Type General Name', 'pfmc-feature-set' ),
		'singular_name' => _x( 'Managed Fishery', 'Post Type Singular Name', 'pfmc-feature-set' ),
		'all_items'     => __( 'All Fisheries', 'pfmc-feature-set' ),
		'add_new'       => __( 'Add New Fishery', 'pfmc-feature-set' ),
		'add_new_item'  => __( 'Add New Managed Fishery', 'pfmc-feature-set' ),
		'edit_item'     => __( 'Edit Fishery', 'pfmc-feature-set' ),
		'view_item'     => __( 'View Managed Fishery', 'pfmc-feature-set' ),
		'view_items'    => __( 'View Managed Fishery', 'pfmc-feature-set' ),
	);

	$args = array(
		'label'                 => __( 'Managed Fisheries', 'pfmc-feature-set' ),
		'labels'                => $labels,
		'description'           => '',
		'public'                => true,
		'publicly_queryable'    => true,
		'show_ui'               => true,
		'delete_with_user'      => false,
		'show_in_rest'          => true,
		'rest_base'             => '',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
		'has_archive'           => true,
		'show_in_menu'          => true,
		'show_in_nav_menus'     => true,
		'exclude_from_search'   => false,
		'capability_type'       => 'post',
		'map_meta_cap'          => true,
		'hierarchical'          => false,
		'rewrite'               => array(
			'slug'       => 'managed_fishery',
			'with_front' => true,
		),
		'query_var'             => true,
		'menu_position'         => 20,
		'menu_icon'             => 'dashicons-shield',
		'supports'              => array(
			'title',
			'editor',
			'thumbnail',
			'excerpt',
		),
		'taxonomies'            => array(
			'category',
			'post_tag',
		),
	);

	\register_post_type( 'managed_fishery', $args );
}
