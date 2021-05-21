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
add_filter( 'get_the_archive_title', __NAMESPACE__ . '\filter_managed_fishery_connect_archive_title', 10, 2 );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_pinned_term_script' );
add_action( 'init', __NAMESPACE__ . '\register_council_meeting_connect_term_meta' );
add_action( 'council_meeting_connect_add_form_fields', __NAMESPACE__ . '\add_new_council_meeting_connect_term_meta_field' );
add_action( 'council_meeting_connect_edit_form_fields', __NAMESPACE__ . '\edit_council_meeting_connect_term_meta_field' );
add_action( 'edit_council_meeting_connect', __NAMESPACE__ . '\save_council_meeting_connect_term_meta' );
add_action( 'create_council_meeting_connect', __NAMESPACE__ . '\save_council_meeting_connect_term_meta' );
add_filter( 'rest_council_meeting_connect_query', __NAMESPACE__ . '\filter_rest_api_query', 11, 2 );

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

/**
 * Filter page title on `managed_fishery_connect` taxonomy term archive views.
 *
 * @param string $title          Archive title to be displayed.
 * @param string $original_title Archive title without prefix.
 * @return string
 */
function filter_managed_fishery_connect_archive_title( $title, $original_title ) {
	if ( is_tax( 'managed_fishery_connect' ) ) {
		$title = __( 'News & Events: ', 'pfmc-feature-set' ) . $original_title;
	}

	return $title;
}

/**
 * Enqueue script for filtering the Council Meeting Connect UI in the editor.
 */
function enqueue_pinned_term_script() {
	$asset_data = require_once dirname( __DIR__ ) . '/js/build/council-meeting-connect-panel.asset.php';

	wp_enqueue_script(
		'pfmc-council-meeting-connect-panel',
		plugins_url( 'js/build/council-meeting-connect-panel.js', dirname( __FILE__ ) ),
		$asset_data['dependencies'],
		$asset_data['version'],
		true
	);
}

/**
 * Register `_pinned` term meta for the Council Meeting Connect taxonomy.
 */
function register_council_meeting_connect_term_meta() {
	register_term_meta(
		'council_meeting_connect',
		'_pinned',
		array(
			'auth_callback' => function() {
				return current_user_can( 'manage_categories' );
			},
			'default'       => false,
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'boolean',
		)
	);
}

/**
 * Display the checkbox for capturing `_pinned` meta to
 * the Council Meeting Connect "Add New Term" form.
 *
 * @param string $taxonomy The taxonomy slug.
 */
function add_new_council_meeting_connect_term_meta_field() {
	wp_nonce_field( 'save_term_meta', 'term_meta_nonce' ); ?>
	<div class="form-field term-meta-text-wrap">
		<label>
			<input type="checkbox" name="_pinned" />
			<?php esc_html_e( 'Pin to top of "Council Meeting Connect" panel', 'pfmc-feature-set' ); ?>
		</label>
	</div>
	<?php
}

/**
 * Display the checkbox for capturing `_pinned` meta
 * to the Council Meeting Connect "Edit Term" form.
 *
 * @param WP_Term $tag Current taxonomy term object.
 */
function edit_council_meeting_connect_term_meta_field( $term ) {
	$pinned = get_term_meta( $term->term_id, '_pinned', true );
	wp_nonce_field( 'save_term_meta', 'term_meta_nonce' );
	?>
	<tr class="form-field term-meta-text-wrap">
		<th scope="row">
			<label for="pfmc-pinned"><?php esc_html_e( 'Pin to top of "Council Meeting Connect" panel', 'pfmc-feature-set' ); ?></label>
		</th>
		<td>
			<input type="checkbox" name="_pinned" id="pfmc-pinned" <?php checked( $pinned ); ?> />
		</td>
	</tr>
	<?php
}

/**
 * Save `_pinned` term meta.
 *
 * @param int $term_id Term ID.
 */
function save_council_meeting_connect_term_meta( $term_id ) {
	if ( ! isset( $_POST['term_meta_nonce'] ) || ! wp_verify_nonce( $_POST['term_meta_nonce'], 'save_term_meta' ) ) {
		return;
	}

	if ( isset( $_POST['_pinned'] ) ) {
		update_term_meta( $term_id, '_pinned', 1 );
	} else {
		delete_term_meta( $term_id, '_pinned' );
	}
}

/**
 * Allow querying terms by meta via the REST API.
 *
 * @param array           $prepared_args Array of arguments to be passed to get_terms().
 * @param WP_REST_Request $request       The REST API request.
 * @return array Modified array of arguments.
 */
function filter_rest_api_query( $prepared_args, $request ) {
	$prepared_args['meta_key']   = $request['meta_key'];
	$prepared_args['meta_value'] = $request['meta_value'];

	return $prepared_args;
}
