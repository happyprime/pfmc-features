<?php
/**
 * Handling for the Actions post type.
 *
 * @package PFMC_Feature_Set
 */

namespace PFMCFS\Post_Type\Actions;

add_action( 'init', __NAMESPACE__ . '\register_post_type', 10 );
add_action( 'init', __NAMESPACE__ . '\register_taxonomies', 10 );

/**
 * Register the Actions post type.
 */
function register_post_type() {

	$labels = array(
		'name'          => _x( 'Actions', 'Post Type General Name', 'pfmc-feature-set' ),
		'singular_name' => _x( 'Action', 'Post Type Singular Name', 'pfmc-feature-set' ),
		'add_new'       => __( 'Add New Action', 'pfmc-feature-set' ),
	);

	$args = array(
		'label'                 => __( 'Actions', 'pfmc-feature-set' ),
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
			'slug'       => 'actions',
			'with_front' => true,
		),
		'query_var'             => true,
		'menu_position'         => 20,
		'menu_icon'             => 'dashicons-portfolio',
		'supports'              => array(
			'title',
			'editor',
			'thumbnail',
			'sticky',
		),
		'taxonomies'            => array(
			'category',
			'post_tag',
		),
	);

	\register_post_type( 'actions', $args );
}

/**
 * Register the Action Types and Action Groupings taxonomies.
 */
function register_taxonomies() {

	// Action Types.
	$labels = array(
		'name'          => __( 'Action Types', 'pfmc-feature-set' ),
		'singular_name' => __( 'Action Type', 'pfmc-feature-set' ),
	);

	$args = array(
		'label'                 => __( 'Action Types', 'pfmc-feature-set' ),
		'labels'                => $labels,
		'public'                => true,
		'publicly_queryable'    => true,
		'hierarchical'          => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'show_in_nav_menus'     => true,
		'query_var'             => true,
		'rewrite'               => array(
			'slug'       => 'action_types',
			'with_front' => true,
		),
		'show_admin_column'     => true,
		'show_in_rest'          => true,
		'rest_base'             => 'action_types',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
		'show_in_quick_edit'    => true,
	);

	register_taxonomy( 'action_types', array( 'actions' ), $args );

	// Action Groupings.
	$labels = array(
		'name'          => __( 'Action Groupings', 'pfmc-feature-set' ),
		'singular_name' => __( 'Action Grouping', 'pfmc-feature-set' ),
	);

	$args = array(
		'label'                 => __( 'Action Groupings', 'pfmc-feature-set' ),
		'labels'                => $labels,
		'public'                => true,
		'publicly_queryable'    => true,
		'hierarchical'          => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'show_in_nav_menus'     => true,
		'query_var'             => true,
		'rewrite'               => array(
			'slug'       => 'action_grouping',
			'with_front' => true,
		),
		'show_admin_column'     => true,
		'show_in_rest'          => true,
		'rest_base'             => 'action_grouping',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
		'show_in_quick_edit'    => true,
	);

	register_taxonomy( 'action_grouping', array( 'actions' ), $args );
}
