<?php
/**
 * Sticky support handling for custom post types.
 *
 * @package PFMC_Feature_Set
 */

namespace PFMCFS\CPT_Sticky_Support;

add_action( 'init', __NAMESPACE__ . '\add_sticky_status_view', 11 );
add_filter( 'query_vars', __NAMESPACE__ . '\filter_query_vars' );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\block_editor_sticky_checkbox' );
add_action( 'post_submitbox_misc_actions', __NAMESPACE__ . '\classic_editor_sticky_checkbox' );
add_action( 'save_post', __NAMESPACE__ . '\save_sticky_status', 10, 2 );

/**
 * Hooks into `views_edit-{$post_type}` for post types with sticky support.
 */
function add_sticky_status_view() {
	$custom_post_types = get_post_types(
		array(
			'public'   => true,
			'_builtin' => false,
		)
	);

	foreach ( $custom_post_types as $post_type ) {
		if ( ! post_type_supports( $post_type, 'sticky' ) ) {
			continue;
		}

		add_filter( "views_edit-{$post_type}", __NAMESPACE__ . '\sticky_status_view' );
		add_action( "rest_insert_{$post_type}", __NAMESPACE__ . '\rest_save_sticky_status', 10, 2 );
	}
}

/**
 * Adds a "Sticky" view link for post types with sticky support.
 *
 * @param array $views Fully-formed view links.
 * @return array Modified array of views.
 */
function sticky_status_view( $views ) {
	$current_screen = get_current_screen();

	// Set up arguments to query for sticky posts of the current type.
	$sticky_posts_count_args = array(
		'post_type' => $current_screen->post_type,
		'post__in'  => get_option( 'sticky_posts' ),
		'fields'    => 'ids',
	);

	// Get the number of sticky posts of the current type.
	$sticky_posts_count = ( new \WP_Query( $sticky_posts_count_args ) )->found_posts;

	$class = ( get_query_var( 'show_sticky', 0 ) ) ? 'current' : '';

	$sticky_inner_html = sprintf(
		/* translators: %s: Number of posts. */
		_nx(
			'Sticky <span class="count">(%s)</span>',
			'Sticky <span class="count">(%s)</span>',
			$sticky_posts_count,
			'posts'
		),
		number_format_i18n( $sticky_posts_count )
	);

	$url = esc_url(
		add_query_arg(
			array( 'show_sticky' => '1' ),
			admin_url( $current_screen->parent_file )
		)
	);

	$class_html   = '';
	$aria_current = '';

	if ( ! empty( $class ) ) {
		$class_html = sprintf(
			' class="%s"',
			esc_attr( $class )
		);

		if ( 'current' === $class ) {
			$aria_current = ' aria-current="page"';
		}
	}

	// Add the Sticky view link.
	$views['sticky'] = sprintf(
		'<a href="%s"%s%s>%s</a>',
		esc_url( $url ),
		$class_html,
		$aria_current,
		$sticky_inner_html
	);

	return $views;
}

/**
 * Adds the `show_sticky` query variable.
 *
 * @param array $public_query_vars Array of public query variable names.
 * @return array $public_query_vars Array of modified public query variable names.
 */
function filter_query_vars( $public_query_vars ) {
	$public_query_vars[] = 'show_sticky';

	return $public_query_vars;
}

/**
 * Enqueues assets for adding a sticky checkbox to the block editor.
 */
function block_editor_sticky_checkbox() {
	$post = get_post();

	if (
		post_type_supports( $post->post_type, 'sticky' )
		&& current_user_can( 'edit_others_posts' )
		&& is_admin()
	) {
		wp_enqueue_script(
			'pfmc-cpt-sticky-checkbox',
			plugins_url( 'js/sticky-checkbox.js', dirname( __FILE__ ) ),
			array(
				'wp-i18n',
				'wp-plugins',
				'wp-edit-post',
				'wp-components',
			),
			'0.0.1',
			true
		);

		wp_localize_script(
			'pfmc-cpt-sticky-checkbox',
			'StickyStatus',
			array(
				'isSticky' => is_sticky( $post->ID ),
			)
		);
	}
}

/**
 * Saves a custom post type's sticky status.
 *
 * @param WP_Post    $post    Inserted or updated post object.
 * @param WP_Request $request Request object.
 */
function rest_save_sticky_status( $post, $request ) {
	if ( current_user_can( 'edit_others_posts' ) ) {
		if ( $request->get_param( 'StickyStatus' ) ) {
			stick_post( $post->ID );
		} else {
			unstick_post( $post->ID );
		}
	}
}

/**
 * Adds the checkbox for making a post sticky to the classic editor interface.
 *
 * @param WP_Post $post WP_Post object.
 */
function classic_editor_sticky_checkbox( $post ) {
	if ( post_type_supports( $post->post_type, 'sticky' ) && current_user_can( 'edit_others_posts' ) ) {
		wp_nonce_field( 'pfmc_save_post_sticky_status', 'pfmc_post_sticky_nonce' );
		?>
		<span id="sticky-span">
			<input id="sticky" name="_pfmc_sticky_status" type="checkbox" <?php checked( is_sticky( $post->ID ) ); ?> />
			<label for="sticky" class="selectit"><?php esc_html_e( 'Stick to the top of the blog', '' ); ?></label><br />
		</span>
		<?php
	}
}

/**
 * Updates a post's sticky status.
 *
 * @param int    $post_id Post ID.
 * @param WP_Post $post   Post object.
 */
function save_sticky_status( $post_id, $post ) {
	// Return early if the nonce is not set or cannot be verified.
	if (
		! isset( $_POST['pfmc_post_sticky_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pfmc_post_sticky_nonce'] ) ), 'pfmc_save_post_sticky_status' )
	) {
		return;
	}

	if ( isset( $_POST['_pfmc_sticky_status'] ) ) {
		stick_post( $post_id );
	} else {
		unstick_post( $post_id );
	}
}
