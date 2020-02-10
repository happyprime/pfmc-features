<?php
/**
 * Handling for the Alert bar.
 *
 * @package PFMC_Feature_Set
 */

namespace PFMCFS\Alert;

add_action( 'init', __NAMESPACE__ . '\register_post_type', 10 );

/**
 * Register the Alert post type.
 */
function register_post_type() {

	$args = array(
		'label'               => __( 'Alerts', 'pfmc-feature-set' ),
		'labels'              => array(
			'name'          => _x( 'Alerts', 'Post Type General Name', 'pfmc-feature-set' ),
			'singular_name' => _x( 'Alert', 'Post Type Singular Name', 'pfmc-feature-set' ),
			'add_new'       => __( 'Add New Alert', 'pfmc-feature-set' ),
		),
		'description'         => '',
		'public'              => true,
		'exclude_from_search' => true,
		'show_in_nav_menus'   => false,
		'show_in_rest'        => true,
		'menu_position'       => 25,
		'menu_icon'           => 'dashicons-warning',
		'supports'            => array(
			'title',
			'editor',
			'excerpt',
			'author',
			'revisions',
		),
		'delete_with_user'    => false,
	);

	\register_post_type( 'alert', $args );
}
