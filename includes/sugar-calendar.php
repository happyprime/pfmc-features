<?php
/**
 * Custom handling of Sugar Calendar plugin.
 *
 * @package PFMC_Feature_Set
 */

namespace PFMCFS\SugarCalendar;

add_filter( 'register_post_type_args', __NAMESPACE__ . '\filter_post_type_args', 10, 2 );
add_action( 'init', __NAMESPACE__ . '\register_categories_for_events', 11 );
add_action( 'after_setup_theme', __NAMESPACE__ . '\remove_default_shortcode_registration' );
add_filter( 'sc_calendar_dropdown_categories_args', __NAMESPACE__ . '\filter_calendar_dropdown_categories_args', 10 );
add_action( 'init', __NAMESPACE__ . '\add_shortcodes' );
add_filter( 'sc_events_query_clauses', __NAMESPACE__ . '\sugar_calendar_join_by_taxonomy_term', 15, 2 );
add_action( 'sc_parse_events_query', __NAMESPACE__ . '\sugar_calendar_pre_get_events_by_taxonomy', 15 );
add_action( 'add_meta_boxes_sc_event', __NAMESPACE__ . '\add_meta_boxes' );
add_action( 'save_post_sc_event', __NAMESPACE__ . '\generate_post', 10, 2 );
add_action( 'wp_trash_post', __NAMESPACE__ . '\delete_event_generated_post', 10 );
add_action( 'template_redirect', __NAMESPACE__ . '\redirect_event_generated_posts', 10 );
add_action( 'pre_get_posts', __NAMESPACE__ . '\filter_managed_fisheries_connect_query', 1000 );
add_filter( 'the_content', __NAMESPACE__ . '\remove_content_hooks', 9 );
add_filter( 'the_excerpt', __NAMESPACE__ . '\remove_content_hooks', 9 );

/**
 * Expose the `sc_event` post type in the REST API.
 *
 * @param array  $args      Array of arguments for registering a post type.
 * @param string $post_type Post type key.
 */
function filter_post_type_args( $args, $post_type ) {
	if ( 'sc_event' === $post_type ) {
		$args['show_in_rest'] = true;
	}

	return $args;
}

/**
 * Register categories for the `sc_event` post type.
 */
function register_categories_for_events() {
	register_taxonomy_for_object_type( 'category', 'sc_event' );
}

/**
 * Remove the default shortcode registration provided by the Sugar Calendar plugin.
 */
function remove_default_shortcode_registration() {
	remove_action( 'init', 'sc_add_shortcodes' );
}

/**
 * Adjust the arguments passed to wp_dropdown_categories() in the Sugar Calendar
 * calendar selection form.
 *
 * This form is hidden from view on the front end and there are no calendars to
 * choose from.
 */
function filter_calendar_dropdown_categories_args( $args ) {
	$args['selected'] = 0;

	// This is `-1` by default, which causes a 404 when embedded on an archive page.
	$args['option_none_value'] = '';

	return $args;
}

/**
 * Add a replacement handler for the sc_events_list shortcode and add back
 * the default handler for the sc_events_calendar shortcode, which does not
 * require the same adjustments in this theme.
 */
function add_shortcodes() {
	add_shortcode( 'sc_events_list', __NAMESPACE__ . '\display_sc_events_list_shortcode' );
	add_shortcode( 'sc_events_calendar', 'sc_events_calendar_shortcode' );
}

/**
 * Provide a list of custom taxonomies that have registered support
 * for the sc_event post type.
 *
 * @return array A list of taxonomy slugs.
 */
function get_custom_calendar_taxonomies() {
	$available_taxonomies = get_object_taxonomies( 'sc_event' );

	// This is already handled as the "category" attribute in the shortcode by
	// the core Sugar Calendar plugin code.
	$key = array_search( 'sc_event_category', $available_taxonomies, true );
	if ( false !== $key ) {
		unset( $available_taxonomies[ $key ] );
	}

	// This would conflict with the "category" attribute and is not supported as
	// part of the shortcode query.
	$key = array_search( 'category', $available_taxonomies, true );
	if ( false !== $key ) {
		unset( $available_taxonomies[ $key ] );
	}

	return $available_taxonomies;
}

/**
 * Event list shortcode callback.
 *
 * This is forked from Sugar Calendar Lite and updated to add rudimentary support
 * for querying events by custom taxonomy terms.
 *
 * @since 1.0.0
 *
 * @param array $atts    A list of shortcode attributes.
 * @param null  $content Content that may appear inside the shortcode. Unused.
 *
 * @return string HTML representing the shortcode.
 */
function display_sc_events_list_shortcode( $atts, $content = null ) {

	$default_atts = array(
		'display'         => 'upcoming',
		'order'           => '',
		'number'          => '5',
		'category'        => null,
		'show_date'       => null,
		'show_time'       => null,
		'show_categories' => null,
		'show_link'       => null,
	);

	$available_taxonomies = get_custom_calendar_taxonomies();

	foreach ( $available_taxonomies as $taxonomy ) {
		$default_atts[ $taxonomy ] = null;
	}

	$atts = shortcode_atts( $default_atts, $atts );

	$display         = esc_attr( $atts['display'] );
	$order           = esc_attr( $atts['order'] );
	$number          = esc_attr( $atts['number'] );
	$show_date       = esc_attr( $atts['show_date'] );
	$show_time       = esc_attr( $atts['show_time'] );
	$show_categories = esc_attr( $atts['show_categories'] );
	$show_link       = esc_attr( $atts['show_link'] );

	$taxonomies = array(
		'category' => esc_attr( $atts['category'] ),
	);

	foreach ( $available_taxonomies as $taxonomy ) {
		if ( isset( $atts[ $taxonomy ] ) ) {
			$taxonomies[ $taxonomy ] = esc_attr( $atts[ $taxonomy ] );
		}
	}

	$args = array(
		'date'       => $show_date,
		'time'       => $show_time,
		'categories' => $show_categories,
		'link'       => $show_link,
	);

	return get_events_list( $display, $taxonomies, $number, $args, $order );
}

/**
 * Get a formatted list of upcoming or past events from today's date.
 *
 * This is forked from Sugar Calendar Lite and updated to add rudimentary support
 * for querying events by custom taxonomy terms.
 *
 * @see sc_events_list_widget
 *
 * @since 1.0.0
 * @param string $display    Whether to display upcoming, past, or all events.
 * @param array  $taxonomies Taxonomies to use.
 * @param int    $number     Number of events to display.
 * @param array  $show       A series of arguments.
 * @param string $order      The order in which to display events.
 *
 * @return string HTML representing a list of events.
 */
function get_events_list( $display = 'upcoming', $taxonomies = array(), $number = 5, $show = array(), $order = '' ) {

	// Get today, to query before/after. The event date is stored in local time, which
	// wp_date() provides when no timezone is provided.
	$today = wp_date( 'Y-m-d' );

	// Mutate order to uppercase if not empty.
	if ( ! empty( $order ) ) {
		$order = strtoupper( $order );
	} else {
		$order = ( 'past' === $display )
			? 'DESC'
			: 'ASC';
	}

	// Maybe force a default.
	if ( ! in_array( strtoupper( $order ), array( 'ASC', 'DESC' ), true ) ) {
		$order = 'ASC';
	}

	if ( 'upcoming' === $display ) {
		$args = array(
			'object_type' => 'post',
			'status'      => 'publish',
			'orderby'     => 'start',
			'order'       => $order,
			'number'      => $number,
			'start_query' => array(
				'inclusive' => true,
				'after'     => $today,
			),
		);
	} elseif ( 'past' === $display ) {
		$args = array(
			'object_type' => 'post',
			'status'      => 'publish',
			'orderby'     => 'start',
			'order'       => $order,
			'number'      => $number,
			'start_query' => array(
				'inclusive' => true,
				'before'    => $today,
			),
		);
	} else {
		// All events.
		$args = array(
			'object_type' => 'post',
			'status'      => 'publish',
			'orderby'     => 'start',
			'order'       => $order,
			'number'      => $number,
		);
	}

	// Maybe filter by taxonomy term.
	if ( ! empty( $taxonomies['category'] ) ) {
		$args[ sugar_calendar_get_calendar_taxonomy_id() ] = $taxonomies['category'];
		unset( $taxonomies['category'] );
	}

	foreach ( $taxonomies as $taxonomy => $term ) {
		$args[ $taxonomy ] = esc_attr( $term );
	}

	// Query for events.
	$events = sugar_calendar_get_events( $args );

	// Bail if no events.
	if ( empty( $events ) ) {
		return '';
	}

	// Start an output buffer to store these result.
	ob_start();

	do_action( 'sc_before_events_list' );

	// Start an unordered list.
	echo '<ul class="sc_events_list">';

	// Loop through all events.
	foreach ( $events as $event ) {

		// Get the object ID and use it for the event ID (for back compat).
		$event_id = $event->object_id;

		echo '<li class="' . esc_attr( str_replace( 'hentry', '', implode( ' ', get_post_class( 'sc_event', $event_id ) ) ) ) . '">';

		do_action( 'sc_before_event_list_item', $event_id );

		echo '<a href="' . esc_url( get_permalink( $event_id ) ) . '" class="sc_event_link">';
		echo '<span class="sc_event_title">' . wp_kses_post( get_the_title( $event_id ) ) . '</span></a>';

		if ( ! empty( $show['date'] ) ) {
			echo '<span class="sc_event_date">' . esc_html( sc_get_formatted_date( $event_id ) ) . '</span>';
		}

		if ( isset( $show['time'] ) && $show['time'] ) {
			$start_time = sc_get_event_start_time( $event_id );
			$end_time   = sc_get_event_end_time( $event_id );

			if ( $event->is_all_day() ) {
				echo '<span class="sc_event_time">' . esc_html__( 'All-day', 'pfmc-feature-set' ) . '</span>';
			} elseif ( $end_time !== $start_time ) {
				echo '<span class="sc_event_time">' . esc_html( $start_time ) . '&nbsp;&ndash;&nbsp;' . esc_html( $end_time ) . '</span>';
			} elseif ( ! empty( $start_time ) ) {
				echo '<span class="sc_event_time">' . esc_html( $start_time ) . '</span>';
			}
		}

		if ( ! empty( $show['categories'] ) ) {
			$event_categories = get_the_terms( $event_id, 'sc_event_category' );

			if ( $event_categories ) {
				$categories = wp_list_pluck( $event_categories, 'name' );
				echo '<span class="sc_event_categories">' . esc_html( join( $categories, ', ' ) ) . '</span>';
			}
		}

		if ( ! empty( $show['link'] ) ) {
			echo '<a href="' . esc_url( get_permalink( $event_id ) ) . '" class="sc_event_link">';
			echo esc_html__( 'Read More', 'pfmc-feature-set' );
			echo '</a>';
		}

		do_action( 'sc_after_event_list_item', $event_id );

		echo '<br class="clear"></li>';
	}

	// Close the list.
	echo '</ul>';

	// Reset post data - we'll be looping through our own.
	wp_reset_postdata();

	do_action( 'sc_after_events_list' );

	// Return the current buffer and delete it.
	return ob_get_clean();
}

/**
 * Filter events query variables and maybe add the taxonomy and term.
 *
 * This filter is necessary to ensure events queries are cached using the
 * taxonomy and term they are queried by.
 *
 * This is forked from Sugar Calendar Lite and updated to add rudimentary support
 * for querying events by custom taxonomy terms.
 *
 * @since 2.0.0
 *
 * @param object|Query $query The current query being adjusted.
 */
function sugar_calendar_pre_get_events_by_taxonomy( $query ) {

	$available_taxonomies = get_custom_calendar_taxonomies();

	foreach ( $available_taxonomies as $taxonomy ) {
		if ( isset( $query->query_var_originals[ $taxonomy ] ) ) {
			$query->set_query_var( $taxonomy, $query->query_var_originals[ $taxonomy ] );
		}
	}
}

/**
 * Filter events queries and maybe JOIN by taxonomy term relationships
 *
 * This is hard-coded (for now) to provide back-compat with the built-in
 * post-type & taxonomy. It can be expanded to support any/all in future versions.
 *
 * This is forked from Sugar Calendar Lite and updated to add rudimentary support
 * for querying events by custom taxonomy terms.
 *
 * @since 2.0.0
 *
 * @param array        $clauses Clauses to be used in a query.
 * @param object|Query $query   The current query being adjusted.
 *
 * @return array Clauses to be used in a query.
 */
function sugar_calendar_join_by_taxonomy_term( $clauses = array(), $query = false ) {

	$available_taxonomies = get_custom_calendar_taxonomies();

	$join_clauses   = array();
	$join_clauses[] = $clauses['join'];

	$where_clauses   = array();
	$where_clauses[] = $clauses['where'];

	$replacement = 1;

	foreach ( $available_taxonomies as $taxonomy ) {
		if ( isset( $query->query_var_originals[ $taxonomy ] ) ) {
			$tax_query = new \WP_Tax_Query(
				array(
					array(
						'taxonomy' => $taxonomy,
						'terms'    => $query->query_var_originals[ $taxonomy ],
						'field'    => 'slug',
					),
				)
			);

			// Get the clauses as provided by WP_Tax_Query.
			$sql_clauses = $tax_query->get_sql( 'sc_e', 'object_id' );

			// JOIN the table as wptr(n) to avoid conflicts with other term queries. This is admittedly kind of ugly,
			// but works around the abilities we have (as I understand them) with the query classes in Sugar Calendar Lite.
			$join_clause    = str_replace( 'wp_term_relationships ON', 'wp_term_relationships AS wptr' . $replacement . ' ON', $sql_clauses['join'] );
			$join_clause    = str_replace( 'wp_term_relationships.', 'wptr' . $replacement . '.', $join_clause );
			$join_clauses[] = $join_clause;

			// As with the JOIN, rename the wp_term_relationship table to wptr(n) in the WHERE clause.
			$where_clauses[] = str_replace( 'wp_term_relationships', 'wptr' . $replacement, $sql_clauses['where'] );

			// Increment (n) as used in the wptr(n) table name aliases.
			$replacement++;
		}
	}

	// Bring all of the clauses back together as their expected strings.
	$clauses['join']  = implode( '', array_filter( $join_clauses ) );
	$clauses['where'] = implode( '', array_filter( $where_clauses ) );

	return $clauses;
}

/**
 * Adds a meta box for managing post generation/updates.
 */
function add_meta_boxes() {
	add_meta_box(
		'pfmcfs-event-to-post',
		'Generate Post',
		__NAMESPACE__ . '\display_event_to_post_meta_box',
		'sc_event',
		'side',
		'high'
	);
}

/**
 * Displays a meta box used to manage post generation/updates.
 *
 * @param \WP_Post $post The post object.
 */
function display_event_to_post_meta_box( $post ) {
	wp_nonce_field( 'pfmcfs_check_event_to_post', 'pfmcfs_event_to_post_nonce' );

	$generated_post_id = get_post_meta( $post->ID, '_pfmcfs_generated_post_id', true );

	if ( $generated_post_id ) :
		?>
			<p>
			<?php
				printf(
					/* translators: %s: link to edit screen of the generated post */
					wp_kses_post( __( 'Post %s was generated by this event.', 'pfmc-feature-set' ) ),
					'<a href="' . esc_url( get_edit_post_link( $generated_post_id ) ) . '">' . absint( $generated_post_id ) . '</a>'
				);
			?>
			</p>
			<p><?php printf( wp_kses_post( __( 'Changes made to the <em>Details</em> field will sync to the post excerpt. <em>Categories</em> and <em>Managed Fisheries Connect</em> term changes will also sync to the post.', 'pfmc-feature-set' ) ) ); ?></p>
			<input type="hidden" name="_pfmcfs_event_to_post" value="1" />
		<?php
	else :
		?>
			<p>
				<input
					type="checkbox"
					id="pfmcfs-event-to-post"
					name="_pfmcfs_event_to_post"
				>
				<label for="pfmcfs-event-to-post"><?php esc_html_e( 'Generate a post from this event', 'pfmc-feature-set' ); ?></label>
			</p>
			<p><?php printf( wp_kses_post( __( 'The <em>Details</em> field will populate the post excerpt. Selected <em>Categories</em> and <em>Managed Fisheries Connect</em> terms will also be applied to the post.', 'pfmc-feature-set' ) ) ); ?></p>
		<?php
	endif;
}

/**
 * Returns a query for an existing post generated from a Sugar Calendar event.
 *
 * @param string $permalink The permalink of the Sugar Calendar event.
 * @return WP_Query The query for a post generated by the event.
 */
function generated_post_query( $permalink ) {
	$query = new \WP_Query(
		array(
			'meta_key'               => '_sc_event_permalink', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'             => $permalink, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		)
	);

	return $query;
}

/**
 * Generates a post using data from a Sugar Calendar event.
 *
 * @param int     $post_id The post ID.
 * @param WP_Post $post    Post object.
 */
function generate_post( $post_id, $post ) {

	// Return if the user doesn't have edit permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Return early if this is a revision.
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Return early if this is an autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Return early if the event was generated by a Council Meeting post.
	if ( get_post_meta( $post_id, '_council_meeting_permalink', true ) ) {
		return;
	}

	// Return early if the nonce ican't be verified.
	if ( ! isset( $_POST['pfmcfs_event_to_post_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pfmcfs_event_to_post_nonce'] ) ), 'pfmcfs_check_event_to_post' ) ) {
		return;
	}

	// Return early if the "Generate a post from this event" box isn't checked.
	if ( ! isset( $_POST['_pfmcfs_event_to_post'] ) ) {
		return;
	}

	// Get the event post permalink.
	$permalink = esc_url( get_permalink( $post_id ) );

	// Set up the post content.
	$content  = '<!-- wp:paragraph -->';
	$content .= '<p>This post was generated by and redirects to <a href="' . esc_url( $permalink ) . '">' . esc_url( $permalink ) . '</a>.</p>';
	$content .= '<!-- /wp:paragraph -->';

	// Get the event's "Fisheries Connect" term IDs.
	$fishery_connect_term_ids = wp_get_object_terms( $post_id, 'managed_fishery_connect', array( 'fields' => 'ids' ) );

	// Set up data for generating or updating a post.
	$post_data = array(
		'post_title'    => wp_strip_all_tags( $post->post_title ),
		'post_status'   => $post->post_status,
		'post_content'  => $content,
		'post_excerpt'  => $post->post_content,
		'post_category' => wp_get_post_categories( $post_id ),
		'tax_input'     => array(
			'managed_fishery_connect' => $fishery_connect_term_ids,
		),
	);

	// Check for a post that has already been generated by this event.
	$query = generated_post_query( $permalink );

	// If a post was found, update its data, then bail.
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();

			// Add the post ID to the data to ensure the correct post is updated.
			$post_data['ID'] = get_the_ID();

			wp_update_post( $post_data );
		}

		return;
	}

	// Create a post using the event data.
	$generated_post = wp_insert_post(
		array_merge(
			$post_data,
			array(
				'meta_input' => array(
					'_sc_event_permalink' => $permalink,
				),
			)
		)
	);

	// If a post was successfully created, store its ID in the event post meta.
	if ( $generated_post ) {
		update_post_meta( $post_id, '_pfmcfs_generated_post_id', absint( $generated_post ) );
	}
}

/**
 * Deletes a post if its associated Sugar Calendar event is trashed.
 *
 * @param int $post_id The post ID.
 */
function delete_event_generated_post( $post_id ) {

	// Return early if the trashed post is not of the `sc_event` type.
	if ( 'sc_event' !== get_post_type( $post_id ) ) {
		return;
	}

	// Check for a post generated by this event.
	$query = generated_post_query( esc_url( get_permalink( $post_id ) ) );

	// If a post was found, delete it.
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			wp_delete_post( get_the_ID() );
		}
	}
}

/**
 * Redirects posts generated from Sugar Calendar events to the event.
 */
function redirect_event_generated_posts() {

	// Return early if this isn't a post.
	if ( ! is_single() ) {
		return;
	}

	// Get the current post ID.
	$post_id = get_queried_object_id();

	// Attempt to retrieve a value from the `_sc_event_permalink` meta key.
	$permalink = get_post_meta( $post_id, '_sc_event_permalink', true );

	// Return early if the post has no `_sc_event_permalink` meta.
	if ( ! $permalink ) {
		return;
	}

	// Redirect to the associated Sugar Calendar event post.
	wp_safe_redirect( esc_url( $permalink ), 301 );

	exit;
}

/**
 * Unhook Sugar Calendar filters for Managed Fishery Connect taxonomy queries,
 * and set the post type parameter to `post`.
 *
 * @param WP_Query $query The WP_Query instance.
 */
function filter_managed_fisheries_connect_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( is_tax( 'managed_fishery_connect' ) ) {
		$query->set( 'post_type', 'post' );

		remove_filter( 'posts_where', 'sc_modify_events_archive_where', 10, 2 );
		remove_filter( 'posts_join', 'sc_modify_events_archive_join', 10, 2 );
		remove_filter( 'posts_orderby', 'sc_modify_events_archive_orderby', 10, 2 );
	}
}

/**
 * Remove the content and excerpt filter hooks from non-event views.
 *
 * @param string $content Content or excerpt of the current post.
 * @return string
 */
function remove_content_hooks( $content ) {
	if ( ! is_singular( 'sc_event' ) && ! is_post_type_archive( 'sc_event' ) ) {
		remove_filter( current_filter(), 'sc_event_content_hooks' );
	}

	return $content;
}
