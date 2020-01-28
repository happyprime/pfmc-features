<?php
/**
 * Handling for the shadow taxonomy functionality.
 *
 * @package PFMC_Feature_Set
 */

namespace PFMCFS\Shadow_Taxonomies;

add_action( 'init', __NAMESPACE__ . '\register_taxonomies', 10 );
add_action( 'save_post_managed_fishery', __NAMESPACE__ . '\update_shadow_taxonomy', 10, 2 );
add_action( 'save_post_council_meeting', __NAMESPACE__ . '\update_shadow_taxonomy', 10, 2 );

/**
 * Register the Managed Fisheries and Council Meetings shadow taxonomies.
 *
 * Thanks to https://ttmm.io/tech/wordpress-shadow-taxonomies/.
 */
function register_taxonomies() {

	// Managed Fisheries Connect.
	$labels = array(
		'name'          => __( 'Managed Fisheries Connect', 'pfmc-feature-set' ),
		'singular_name' => __( 'Managed Fishery Connect', 'pfmc-feature-set' ),
	);

	$args = array(
		'label'                 => __( 'Managed Fisheries Connect', 'pfmc-feature-set' ),
		'labels'                => $labels,
		'public'                => true,
		'publicly_queryable'    => true,
		'hierarchical'          => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'show_in_nav_menus'     => true,
		'query_var'             => true,
		'rewrite'               => array(
			'slug'       => 'managed_fishery_connect',
			'with_front' => true,
		),
		'show_admin_column'     => true,
		'show_in_rest'          => true,
		'rest_base'             => 'managed_fishery_connect',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
		'show_in_quick_edit'    => true,
	);

	register_taxonomy( 'managed_fishery_connect', array( 'actions', 'council_meetings', 'document', 'sc_event', 'post' ), $args );

	// Council Meeting Connect.
	$labels = array(
		'name'          => __( 'Council Meeting Connect', 'pfmc-feature-set' ),
		'singular_name' => __( 'Council Meeting Connect', 'pfmc-feature-set' ),
	);

	$args = array(
		'label'                 => __( 'Council Meeting Connect', 'pfmc-feature-set' ),
		'labels'                => $labels,
		'public'                => true,
		'publicly_queryable'    => true,
		'hierarchical'          => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'show_in_nav_menus'     => true,
		'query_var'             => true,
		'rewrite'               => array(
			'slug'       => 'council_meeting_connect',
			'with_front' => true,
		),
		'show_admin_column'     => true,
		'show_in_rest'          => true,
		'rest_base'             => 'council_meeting_connect',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
		'show_in_quick_edit'    => true,
	);

	register_taxonomy( 'council_meeting_connect', array( 'actions', 'document', 'post', 'sc_event' ), $args );
}

/**
 * When a `managed_fishery` or `council_meeting` post is published,
 * automatically create a term in its respective shadow taxonomy.
 *
 * @uses get_term_by()
 * @uses wp_insert_term()
 *
 * @const DOING_AUTOSAVE
 *
 * @param int     $post_id The post ID.
 * @param WP_Post $post    Post object.
 */
function update_shadow_taxonomy( $post_id, $post ) {

	// If we're running an auto-save, don't create a term.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Don't create a term the post is not published or it has no title.
	if ( 'publish' !== $post->post_status || '' === $post->post_title ) {
		return;
	}

	// If `post_date` and `post_modified` aren't equal,
	// don't create a term because this must be an update.
	if ( $post->post_date !== $post->post_modified ) {
		return;
	}

	// Build the shadow taxonomy slug using the post type slug.
	$taxonomy = "{$post->post_type}_connect";

	// Stop if there is already a term with the same name as this post title.
	if ( get_term_by( 'name', $post->post_title, $taxonomy ) ) {
		return;
	}

	// Create a new term using the title of this post as a name.
	wp_insert_term( $post->post_title, $taxonomy );
}
