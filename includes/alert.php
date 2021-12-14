<?php
/**
 * Handling for the Alert bar.
 *
 * @package PFMC_Features
 */

namespace PFMCFS\Alert;

add_action( 'init', __NAMESPACE__ . '\register_post_type', 10 );
add_action( 'save_post_alert', __NAMESPACE__ . '\save_post_meta', 10, 2 );
add_action( 'wp_trash_post', __NAMESPACE__ . '\delete_alert_transient', 10 );
add_action( 'wp_body_open', __NAMESPACE__ . '\display_alert_bar', 10 );

/**
 * Register the Alert post type.
 */
function register_post_type() {

	$args = array(
		'label'                => __( 'Alerts', 'pfmc-feature-set' ),
		'labels'               => array(
			'name'          => _x( 'Alerts', 'Post Type General Name', 'pfmc-feature-set' ),
			'singular_name' => _x( 'Alert', 'Post Type Singular Name', 'pfmc-feature-set' ),
			'add_new'       => __( 'Add New Alert', 'pfmc-feature-set' ),
		),
		'description'          => '',
		'public'               => true,
		'exclude_from_search'  => true,
		'show_in_nav_menus'    => false,
		'show_in_rest'         => true,
		'menu_position'        => 30,
		'menu_icon'            => 'dashicons-warning',
		'supports'             => array(
			'title',
			'editor',
			'excerpt',
			'author',
			'revisions',
		),
		'register_meta_box_cb' => __NAMESPACE__ . '\add_meta_boxes',
		'delete_with_user'     => false,
	);

	\register_post_type( 'alert', $args );
}

/**
 * Adds a meta box for managing alert level and display duration.
 */
function add_meta_boxes() {
	add_meta_box(
		'pfmcfs-alert',
		'Alert Settings',
		__NAMESPACE__ . '\display_alert_meta_box',
		'alert',
		'side',
		'high'
	);
}

/**
 * Returns an array of alert level field labels keyed by id.
 *
 * @return array Field values keyed by id.
 */
function get_alert_level_fields() {
	return array(
		'low'    => __( 'Announcement', 'pfmc-feature-set' ),
		'medium' => __( 'High-level announcement', 'pfmc-feature-set' ),
		'high'   => __( 'Safety alert', 'pfmc-feature-set' ),
	);
}

/**
 * Returns the time until transient expiration in seconds.
 *
 * @param string $display_through Date through which the alert should be shown.
 * @return int Seconds through transient expiration.
 */
function get_expiration( $display_through ) {
	$today   = strtotime( gmdate( 'Y-m-d H:i:s' ) );
	$through = strtotime( $display_through );

	return $through - $today;
}

/**
 * Displays a meta box used to manage alert level and display duration.
 *
 * @param \WP_Post $post The post object.
 */
function display_alert_meta_box( $post ) {
	wp_nonce_field( 'pfmcfs_check_alert', 'pfmcfs_alert_nonce' );

	// Get existing meta values.
	$level   = get_post_meta( $post->ID, '_pfmcfs_alert_level', true );
	$through = get_post_meta( $post->ID, '_pfmcfs_alert_display_through', true );

	// Set `low` as the default alert level.
	$level = ( $level ) ? $level : 'low';

	// Set the default minimum as today.
	// Seconds are intentionally left out for nicer display in the time input.
	$through_default = explode( ' ', gmdate( 'Y-m-d H:i' ) );

	// Set the default "Display alert through" value as one day from now.
	$through = ( $through ) ? $through : wp_date( 'Y-m-d H:i', strtotime( '+1 day' ) );
	$through = explode( ' ', $through );

	?>
	<p><?php esc_html_e( 'Alert level', 'pfmc-feature-set' ); ?></p>
	<?php

	foreach ( get_alert_level_fields() as $id => $label ) :
		?>
		<p>
			<input
				type="radio"
				id="pfmcfs-alert_level-<?php echo esc_attr( $id ); ?>"
				name="_pfmcfs_alert_level"
				value="<?php echo esc_attr( $id ); ?>"
				<?php checked( $level, $id ); ?>
			>
			<label for="pfmcfs-alert_level-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
		</p>
		<?php
	endforeach;

	?>
	<p>
		<label for="pfmcfs-alert_display-through"><?php esc_html_e( 'Display alert through', 'pfmc-feature-set' ); ?></label>
		<input
			type="date"
			id="pfmcfs-alert_display-through-date"
			name="_pfmcfs_alert_display_through_date"
			value="<?php echo esc_attr( $through[0] ); ?>"
			min="<?php echo esc_attr( $through_default[0] ); ?>"
		/>
		<input
			type="time"
			id="pfmcfs-alert_display-through-time"
			name="_pfmcfs_alert_display_through_time"
			value="<?php echo esc_attr( $through[1] ); ?>"
			min="<?php echo esc_attr( $through_default[1] ); ?>"
		/>
	</p>
	<?php
}

/**
 * Saves alert post meta.
 *
 * @param int     $post_id The post ID.
 * @param WP_Post $post    Post object.
 */
function save_post_meta( $post_id, $post ) {

	/**
	 * Return early if:
	 *     the user doesn't have edit permissions;
	 *     this is an autosave;
	 *     this is a revision; or
	 *     the nonce can't be verified.
	 */
	if (
		( ! current_user_can( 'edit_post', $post_id ) )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| wp_is_post_revision( $post_id )
		|| ( ! isset( $_POST['pfmcfs_alert_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pfmcfs_alert_nonce'] ) ), 'pfmcfs_check_alert' ) )
		|| 'publish' !== $post->post_status
	) {
		return;
	}

	// Set up intial data to store in a transient.
	$alert_data = array(
		'heading' => $post->post_title,
		'content' => $post->post_excerpt,
		'url'     => get_the_permalink( $post_id ),
	);

	// Set up the initial expiration for the transient (none by default).
	$expiration = 0;

	if ( isset( $_POST['_pfmcfs_alert_level'] ) && in_array( $_POST['_pfmcfs_alert_level'], array_keys( get_alert_level_fields() ), true ) ) {
		$level = sanitize_text_field( wp_unslash( $_POST['_pfmcfs_alert_level'] ) );

		// Add the alert level to the transient data.
		$alert_data['level'] = $level;

		update_post_meta( $post_id, '_pfmcfs_alert_level', $level );
	}

	if ( isset( $_POST['_pfmcfs_alert_display_through_date'] ) && '' !== sanitize_text_field( wp_unslash( $_POST['_pfmcfs_alert_display_through_date'] ) ) ) {
		$display_through  = sanitize_text_field( wp_unslash( $_POST['_pfmcfs_alert_display_through_date'] ) );
		$display_through .= ( isset( $_POST['_pfmcfs_alert_display_through_time'] ) && '' !== sanitize_text_field( wp_unslash( $_POST['_pfmcfs_alert_display_through_time'] ) ) )
			? ' ' . sanitize_text_field( wp_unslash( $_POST['_pfmcfs_alert_display_through_time'] ) ) . ':00'
			: ' 23:59:59';

		// Overwrite the expiration for the transient.
		$expiration = get_expiration( $display_through );

		update_post_meta( $post_id, '_pfmcfs_alert_display_through', $display_through );
	}

	set_transient( get_pfmc_alert_transient_key(), $alert_data, $expiration );
}

/**
 * Clear the alert transient when an alert post is trashed.
 *
 * @param int $post_id The post ID.
 */
function delete_alert_transient( $post_id ) {
	if ( 'alert' === get_post_type( $post_id ) ) {
		delete_transient( get_pfmc_alert_transient_key() );
	}
}

/**
 * Outputs the alert bar markup.
 */
function display_alert_bar() {

	// Return early if this is an alert post.
	if ( is_singular( 'alert' ) ) {
		return;
	}

	$alert_data = get_transient( get_pfmc_alert_transient_key() );

	// Query for an alert post if no transient data is available.
	if ( ! $alert_data ) {

		// Set up intial data to store in a transient.
		$alert_data = 'no alert';

		// Set up the initial expiration for the transient (none by default).
		$expiration = 0;

		// Query for an alert post with a `_pfmcfs_alert_display_through`
		// value greater than the current date/time.
		$alert_query = new \WP_Query(
			array(
				'post_type'      => 'alert',
				'posts_per_page' => 1,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_pfmcfs_alert_display_through',
						'value'   => wp_date( 'Y-m-d H:i:s' ),
						'compare' => '>',
						'type'    => 'DATETIME',
					),
				),
			)
		);

		if ( $alert_query->have_posts() ) {
			while ( $alert_query->have_posts() ) {
				$alert_query->the_post();

				// Overwrite the data to store in the transient and make available to the script.
				$alert_data = array(
					'heading' => get_the_title(),
					'content' => get_the_excerpt(),
					'level'   => get_post_meta( get_the_ID(), '_pfmcfs_alert_level', true ),
					'url'     => get_the_permalink(),
				);

				// Overwrite the expiration for the transient.
				$display_through = get_post_meta( get_the_ID(), '_pfmcfs_alert_display_through', true );
				$expiration      = get_expiration( $display_through );
			}
		}

		wp_reset_postdata();

		set_transient( get_pfmc_alert_transient_key(), $alert_data, $expiration );
	}

	// Return early if there is no alert data.
	if ( 'no alert' === $alert_data ) {
		return;
	}

	// Low level alerts should display only on the home page.
	if ( 'low' === $alert_data['level'] && ! is_front_page() ) {
		return;
	}

	$classes  = 'pfmc-alert';
	$classes .= ' ' . $alert_data['level'];

	?>
	<div class="<?php echo esc_attr( $classes ); ?>">
		<h1><?php echo esc_attr( $alert_data['heading'] ); ?></h1>
		<p><a href="<?php echo esc_url( $alert_data['url'] ); ?>"><?php echo wp_kses_post( $alert_data['content'] ); ?></a></p>
	</div>
	<?php
}
