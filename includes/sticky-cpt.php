<?php
/**
 * Sticky support handling for custom post types.
 *
 * @package PFMC_Feature_Set
 */

namespace PFMCFS\CPT_Sticky_Support;

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\block_editor_sticky_checkbox' );
add_action( 'rest_insert_post', __NAMESPACE__ . '\rest_save_sticky_status', 10, 2 );
add_action( 'post_submitbox_misc_actions', __NAMESPACE__ . '\classic_editor_sticky_checkbox' );
add_action( 'save_post', __NAMESPACE__ . '\save_sticky_status', 10, 2 );

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
function rest_save_editor_template( $post, $request ) {
	if (
		post_type_supports( $post->post_type, 'sticky' )
		&& current_user_can( 'edit_others_posts' )
		&& $request->get_param( 'StickyStatus' )
	) {
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
