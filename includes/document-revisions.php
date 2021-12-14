<?php
/**
 * Custom handling for the WP Document Revisions plugin.
 *
 * @package PFMC_Features
 */

namespace PFMCFS\WP_Document_Revisions;

add_filter( 'register_post_type_args', __NAMESPACE__ . '\filter_post_type_args', 10, 2 );
add_action( 'init', __NAMESPACE__ . '\register_categories_for_documents', 11 );
add_action( 'init', __NAMESPACE__ . '\register_taxonomies', 10 );
add_action( 'init', __NAMESPACE__ . '\add_sticky_support', 10 );

/**
 * Expose the `document` post type in the REST API.
 *
 * @param array  $args      Array of arguments for registering a post type.
 * @param string $post_type Post type key.
 */
function filter_post_type_args( $args, $post_type ) {
	if ( 'document' === $post_type ) {
		$args['show_in_rest'] = true;
	}

	return $args;
}

/**
 * Register categories for the `document` post type.
 */
function register_categories_for_documents() {
	register_taxonomy_for_object_type( 'category', 'document' );
}

/**
 * Register Document Types and Document Groupings taxonomies.
 */
function register_taxonomies() {

	// Document Types.
	$labels = array(
		'name'          => __( 'Document Types', 'pfmc-feature-set' ),
		'singular_name' => __( 'Document Type', 'pfmc-feature-set' ),
	);

	$args = array(
		'label'                 => __( 'Document Types', 'pfmc-feature-set' ),
		'labels'                => $labels,
		'public'                => true,
		'publicly_queryable'    => true,
		'hierarchical'          => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'show_in_nav_menus'     => true,
		'query_var'             => true,
		'rewrite'               => array(
			'slug'       => 'document_types',
			'with_front' => true,
		),
		'show_admin_column'     => true,
		'show_in_rest'          => true,
		'rest_base'             => 'document_types',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
		'show_in_quick_edit'    => true,
	);

	register_taxonomy( 'document_types', array( 'document' ), $args );

	// Document Groups.
	$labels = array(
		'name'          => __( 'Document Groups', 'pfmc-feature-set' ),
		'singular_name' => __( 'Document Group', 'pfmc-feature-set' ),
	);

	$args = array(
		'label'                 => __( 'Document Group', 'pfmc-feature-set' ),
		'labels'                => $labels,
		'public'                => true,
		'publicly_queryable'    => true,
		'hierarchical'          => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'show_in_nav_menus'     => true,
		'query_var'             => true,
		'rewrite'               => array(
			'slug'       => 'document_group',
			'with_front' => true,
		),
		'show_admin_column'     => true,
		'show_in_rest'          => true,
		'rest_base'             => 'document_group',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
		'show_in_quick_edit'    => true,
	);

	register_taxonomy( 'document_group', array( 'document' ), $args );
}

/**
 * Adds sticky support to the `document` post type.
 */
function add_sticky_support() {
	add_post_type_support( 'document', 'sticky' );
}
