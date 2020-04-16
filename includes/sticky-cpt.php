<?php
/**
 * Sticky support handling for custom post types.
 *
 * @package PFMC_Feature_Set
 */

namespace PFMCFS\CPT_Sticky_Support;

add_action( 'init', __NAMESPACE__ . '\init', 11 );
add_filter( 'query_vars', __NAMESPACE__ . '\filter_query_vars' );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\block_editor_sticky_checkbox' );
add_action( 'post_submitbox_misc_actions', __NAMESPACE__ . '\classic_editor_sticky_checkbox' );
add_action( 'quick_edit_custom_box', __NAMESPACE__ . '\quick_edit_sticky_checkbox' );
add_action( 'bulk_edit_custom_box', __NAMESPACE__ . '\bulk_edit_sticky_select' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_inline_edit_scripts' );
add_action( 'wp_ajax_save_bulk_edit_sticky_status', __NAMESPACE__ . '\save_bulk_edit_sticky_status' );

/**
 * Adds post type specific hooks for post types with sticky support.
 */
function init() {
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
		add_action( "save_post_{$post_type}", __NAMESPACE__ . '\save_sticky_status' );
		add_filter( "manage_{$post_type}_posts_columns", __NAMESPACE__ . '\add_sticky_column' );
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
		$checked = 'add' !== get_current_screen()->action && is_sticky( $post->ID );
		?>
		<div class="misc-pub-section pfmc-sticky">
			<?php wp_nonce_field( 'pfmc_save_post_sticky_status', 'pfmc_post_sticky_nonce' ); ?>
			<span id="sticky-span">
				<input id="sticky" name="_pfmc_sticky_status" type="checkbox" <?php checked( $checked ); ?> />
				<label for="sticky" class="selectit"><?php esc_html_e( 'Stick to the top of the blog', '' ); ?></label><br />
			</span>
		</div>
		<?php
	}
}

/**
 * Updates a post's sticky status.
 *
 * @param int $post_id Post ID.
 */
function save_sticky_status( $post_id ) {
	// Return early if the nonce is not set or cannot be verified,
	// or the user doesn't have adequate permissions.
	if (
		! isset( $_POST['pfmc_post_sticky_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pfmc_post_sticky_nonce'] ) ), 'pfmc_save_post_sticky_status' )
		|| ! current_user_can( 'edit_others_posts' )
	) {
		return;
	}

	if ( $_POST['_pfmc_sticky_status'] ) {
		stick_post( $post_id );
	} else {
		unstick_post( $post_id );
	}
}

/**
 * Adds a "Sticky" column to the post list table.
 *
 * This is visually hidden and leveraged only
 * for adding the quick and bulk edit fields.
 */
function add_sticky_column( $column_array ) {
	$column_array['sticky'] = 'Sticky';

	return $column_array;
}

/**
 * Adds a "Make this post sticky" checkbox to the quick edit interface.
 *
 * @param string $column_name The name of the column to edit.
 */
function quick_edit_sticky_checkbox( $column_name ) {
	if ( 'sticky' !== $column_name || ! current_user_can( 'edit_others_posts' ) ) {
		return;
	}

	?>
	<label class="pfmc-inline-edit-sticky alignleft">
		<?php wp_nonce_field( 'pfmc_save_post_sticky_status', 'pfmc_post_sticky_nonce' ); ?>
		<input id="pfmc-sticky-quick" name="_pfmc_sticky_status" type="checkbox" />
		<span class="checkbox-title"><?php esc_html_e( 'Make this post sticky', '' ); ?></span>
	</label>
	<?php
}

/**
 * Adds a "Make this post sticky" dropdown to the bulk edit interface.
 *
 * @param string $column_name The name of the column to edit.
 */
function bulk_edit_sticky_select( $column_name ) {
	if ( 'sticky' !== $column_name || ! current_user_can( 'edit_others_posts' ) ) {
		return;
	}

	?>
	<label class="pfmc-inline-edit-sticky alignright">
		<?php wp_nonce_field( 'pfmc_save_post_sticky_status', 'pfmc-sticky-bulk-nonce' ); ?>
		<span class="title">Sticky</span>
		<select id="pfmc-sticky-bulk" name="_pfmc_sticky_status">
			<option value=""><?php esc_html_e( '&mdash; No Change &mdash;' ); ?></option>
			<option value="yes"><?php esc_html_e( 'Sticky' ); ?></option>
			<option value="no"><?php esc_html_e( 'Not Sticky' ); ?></option>
		</select>
	</label>
	<?php
}

/**
 * Enqueues the script for handling the `checked` attribute
 * of the sticky checkbox in the quick edit interface.
 *
 * @param string $hook_suffix The current admin page.
 */
function enqueue_inline_edit_scripts( $hook_suffix ) {
	if ( 'edit.php' === $hook_suffix && current_user_can( 'edit_others_posts' ) ) {
		wp_enqueue_script(
			'pfmc-cpt-sticky-inline-edit',
			plugins_url( 'js/sticky-inline-edit.js', dirname( __FILE__ ) ),
			array(),
			'0.0.1',
			true
		);
	}

	if ( in_array( $hook_suffix, array( 'edit.php', 'post-new.php', 'post.php' ), true ) ) {
		wp_enqueue_style(
			'pfmc-cpt-sticky-inline-edit',
			plugins_url( 'css/sticky-inline-edit.css', dirname( __FILE__ ) ),
			array(),
			'0.0.1'
		);
	}
}

/**
 * Saves changes to sticky status made via the bulk edit interface.
 */
function save_bulk_edit_sticky_status() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'pfmc_save_post_sticky_status' ) ) {
		wp_die();
	}

	if ( empty( $_POST['post_ids'] ) || empty( $_POST['sticky'] ) ) {
		wp_die();
	}

	$post_ids = array_map( 'absint', explode( ',', $_POST['post_ids'] ) );

	if ( is_array( $post_ids ) && ! empty( $post_ids ) ) {
		foreach ( $post_ids as $post_id ) {
			if ( 'yes' === $_POST['sticky'] ) {
				stick_post( $post_id );
			} elseif ( 'no' === $_POST['sticky'] ) {
				unstick_post( $post_id );
			}
		}
	}

	wp_die();
}
