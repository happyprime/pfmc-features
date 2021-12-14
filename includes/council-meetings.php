<?php
/**
 * Handling for the Council Meeting post type.
 *
 * @package PFMC_Features
 */

namespace PFMCFS\Post_Type\Council_Meetings;

add_action( 'init', __NAMESPACE__ . '\register_post_type', 10 );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_draggable_assets' );
add_action( 'save_post_council_meeting', __NAMESPACE__ . '\save_meta', 1 );
add_action( 'save_post_council_meeting', __NAMESPACE__ . '\create_event', 10, 2 );
add_action( 'wp_trash_post', __NAMESPACE__ . '\delete_event', 10 );
add_action( 'template_redirect', __NAMESPACE__ . '\redirect_events', 10 );

add_shortcode( 'council_meeting', __NAMESPACE__ . '\render_council_meeting_shortcode', 10 );
add_shortcode( 'past_council_meeting_list', __NAMESPACE__ . '\show_past_council_meetings', 10 );
add_shortcode( 'future_council_meeting_list', __NAMESPACE__ . '\show_future_council_meetings', 10 );

/**
 * Register the Council Meeting post type.
 */
function register_post_type() {

	$labels = array(
		'name'          => _x( 'Council Meetings', 'Post Type General Name', 'pfmc-feature-set' ),
		'singular_name' => _x( 'Council Meeting', 'Post Type Singular Name', 'pfmc-feature-set' ),
		'all_items'     => __( 'All Meetings', 'pfmc-feature-set' ),
		'add_new'       => __( 'Add New Meeting', 'pfmc-feature-set' ),
		'add_new_item'  => __( 'Add New Meeting', 'pfmc-feature-set' ),
		'edit_item'     => __( 'Edit Meeting', 'pfmc-feature-set' ),
	);

	$args = array(
		'label'                 => __( 'Council Meetings', 'pfmc-feature-set' ),
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
			'slug'       => 'council_meeting',
			'with_front' => true,
		),
		'query_var'             => true,
		'menu_position'         => 20,
		'menu_icon'             => 'dashicons-calendar',
		'supports'              => array(
			'title',
			'editor',
			'thumbnail',
			'excerpt',
		),
		'taxonomies'            => array(
			'category',
			'post_tag',
		),
		'template'              => array(
			array(
				'core/columns',
				array(
					'className' => 'layout-two_one',
				),
				array(
					array(
						'core/column',
						array(),
						array(
							array(
								'core/paragraph',
								array(
									'placeholder' => 'Location details',
								),
							),
							array(
								'core/separator',
								array(
									'className' => 'is-style-wide',
								),
							),
							array(
								'core/paragraph',
								array(
									'placeholder' => 'About the meeting',
								),
							),
							array(
								'core/button',
								array(
									'className'   => 'is-style-arrow-link',
									'placeholder' => 'Add public comment text',
								),
							),
						),
					),
					array(
						'core/column',
						array(),
						array(
							array(
								'core/group',
								array(
									'className' => 'block-box',
								),
								array(
									array(
										'core/heading',
										array(
											'level'     => '2',
											'className' => 'block-box-head',
											'content'   => 'Key documents',
										),
									),
									array(
										'happyprime/latest-custom-posts',
										array(),
									),
								),
							),
							array(
								'core/group',
								array(
									'className' => 'block-box',
								),
								array(
									array(
										'core/heading',
										array(
											'level'     => '2',
											'className' => 'block-box-head',
											'content'   => 'Council Meeting updates',
										),
									),
									array(
										'happyprime/latest-custom-posts',
										array(
											'itemCount' => '10',
										),
									),
								),
							),
						),
					),
				),
			),
		),
		'register_meta_box_cb'  => __NAMESPACE__ . '\council_meeting_meta',
	);

	\register_post_type( 'council_meeting', $args );
}

/**
 * Adds a metabox to the right sidebar for Council Meeting CPT
 */
function council_meeting_meta() {
	add_meta_box(
		'council_meeting_start_date',
		'Meeting Start Date',
		__NAMESPACE__ . '\council_meeting_start_date',
		'council_meeting',
		'side',
		'high'
	);

	add_meta_box(
		'council_meeting_end_date',
		'Meeting End Date',
		__NAMESPACE__ . '\council_meeting_end_date',
		'council_meeting',
		'side',
		'high'
	);

	add_meta_box(
		'council_meeting_location',
		'Meeting Location',
		__NAMESPACE__ . '\council_meeting_location',
		'council_meeting',
		'side',
		'high'
	);

	add_meta_box(
		'council_meeting_documents',
		'Meeting Documents',
		__NAMESPACE__ . '\council_meeting_documents',
		'council_meeting',
		'side',
		'high'
	);
}

/**
 * Startdate HTML input for meta in Council Meeting CPT
 *
 * @param WP_Post $post The post object.
 */
function council_meeting_start_date( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'meeting_meta_fields' );
	$meeting_start_date = get_post_meta( $post->ID, 'council_meeting_start_date', true );

	// Convert timestamp back to YYYY-MM-DD that the input field expects.
	if ( '' !== $meeting_start_date ) {
		$meeting_start_date = date( 'Y-m-d', $meeting_start_date ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
	}

	echo '<input type="date" id="council_meeting_start_date" name="council_meeting_start_date" value="' . esc_attr( $meeting_start_date ) . '" />';
}

/**
 * Enddate HTML input for meta in Council Meeting CPT
 *
 * @param WP_Post $post The post object.
 */
function council_meeting_end_date( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'meeting_meta_fields' );
	$meeting_end_date = get_post_meta( $post->ID, 'council_meeting_end_date', true );

	// Convert timestamp back to YYYY-MM-DD that the input field expects.
	if ( '' !== $meeting_end_date ) {
		$meeting_end_date = date( 'Y-m-d', $meeting_end_date ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
	}

	echo '<input type="date" id="council_meeting_end_date" name="council_meeting_end_date" value="' . esc_attr( $meeting_end_date ) . '" />';
}

/**
 * Location HTML input for meta in Council Meeting CPT
 *
 * @param WP_Post $post The post object.
 */
function council_meeting_location( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'meeting_meta_fields' );
	$meeting_location = get_post_meta( $post->ID, 'council_meeting_location', true );
	echo '<input type="text" id="council_meeting_location" name="council_meeting_location" value="' . esc_attr( $meeting_location ) . '" />';
}

/**
 * HTML input for documents linked to Council Meetings.
 *
 * @param WP_Post $post The post object.
 */
function council_meeting_documents( $post ) {
	$documents = get_post_meta( $post->ID, 'council_meeting_documents', true );
	$count     = 0;
	?>
	<div class="dragdrop-sortable-content">
		<ul id="dragdrop-sortable">
			<?php
			if ( is_array( $documents ) && ! empty( $documents ) ) {
				foreach ( $documents as $index => $item ) {
					$count++;
					?>
					<li class="ui-state-default single-sortable-item">
						<div class="dragdrop-sortable-item closed">
							<div class="dragdrop-sortable-item-header">
								<h3 class="hndle">Document <span class="dragdrop-item-count"><?php echo esc_html( $count ); ?></span></h3>
							</div>
							<div class="dragdrop-sortable-item-body">
								<label for="council_meeting_documents[<?php echo esc_html( $index ); ?>][title]">Document <?php echo esc_html( $count ); ?> Title <input type="text" class="widefat" name="council_meeting_documents[<?php echo esc_html( $index ); ?>][title]" id="council_meeting_documents[<?php echo esc_html( $index ); ?>][title]" value="<?php echo empty( $item['title'] ) ? '' : esc_html( $item['title'] ); ?>"></label>
								<label for="council_meeting_documents[<?php echo esc_html( $index ); ?>][url]">Document <?php echo esc_html( $count ); ?> URL <input type="text" class="widefat" name="council_meeting_documents[<?php echo esc_html( $index ); ?>][url]" id="council_meeting_documents[<?php echo esc_html( $index ); ?>][url]" value="<?php echo empty( $item['url'] ) ? '' : esc_html( $item['url'] ); ?>"></label>
								<div class="dragdrop-sortable-item-bottom">
									<button type="button" class="button remove-dragdrop-sortable">Remove Document <span class="dragdrop-item-count"><?php echo esc_html( $count ); ?></span></button>
								</div>
							</div>
							<input type="hidden" name="council_meeting_documents[<?php echo esc_html( $index ); ?>][order]" value="<?php echo esc_html( $index ); ?>" class="dragdrop-set-order">
						</div>
					</li>
					<?php
				}
			} else { // If array is empty or not set
				?>
				<li class="ui-state-default single-sortable-item">
					<div class="dragdrop-sortable-item">
						<div class="dragdrop-sortable-item-header">
							<h3 class="hndle">Document Name <span class="dragdrop-item-count">1</span></h3>
						</div>
						<div class="dragdrop-sortable-item-body">
							<label for="council_meeting_documents[0][title]">Document 1 Title <input type="text" class="widefat" name="council_meeting_documents[0][title]" id="council_meeting_documents[0][title]" value="<?php echo empty( $item['title'] ) ? '' : esc_html( $item['title'] ); ?>"></label>
							<label for="council_meeting_documents[0][url]">Document 1 URL <input type="text" class="widefat" name="council_meeting_documents[0][url]" id="council_meeting_documents[0][url]" value="<?php echo empty( $item['url'] ) ? '' : esc_html( $item['url'] ); ?>"></label>
							<div class="dragdrop-sortable-item-bottom">
								<button type="button" class="button remove-dragdrop-sortable">Remove Document <span class="dragdrop-item-count"><?php echo esc_html( $count ); ?></span></button>
							</div>
						</div>
						<input type="hidden" name="council_meeting_documents[0][order]" value="0" class="dragdrop-set-order">
					</div>
				</li>
				<?php
			}
			?>
		</ul>
		<button type="button" class="button add-dragdrop-sortable">Add Document</button>
	</div>
	<?php
}

/**
 * DRAGGABLE Sort the documents by numeric order.
 */
function fixdragdrop_item_order( $post_array ) {
	$makearray = array();
	foreach ( $post_array as $item_array ) {
		$key               = $item_array['order'];
		$makearray[ $key ] = $item_array;
	}
	ksort( $makearray, SORT_NUMERIC );
	return $makearray;
}

/**
 * Document meta script and stylesheet.
 */
function enqueue_draggable_assets( $pagename ) {
	global $typenow;
	if ( ( 'post.php' === $pagename || 'post-new.php' === $pagename ) && 'council_meeting' === $typenow ) {
		$asset_data = require_once dirname( __DIR__ ) . '/js/build/metabox-draggable.asset.php';
		wp_enqueue_style( 'dragdrop-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css', array(), '4.0.3' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'dragdrop-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array(), '4.0.3', true );
		wp_enqueue_script( 'dragdrop-script', plugins_url( '/js/build/metabox-draggable.js', dirname( __FILE__ ) ), array( 'jquery' ), $asset_data['version'], true );
	}
}

/**
 * Save Council Meeting CPT metabox data
 *
 * @param int $post_id The ID of the post.
 */
function save_meta( $post_id ) {

	// Return if the user doesn't have edit permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times.
	if ( ! isset( $_POST['meeting_meta_fields'] ) || ! wp_verify_nonce( sanitize_key( $_POST['meeting_meta_fields'] ), basename( __FILE__ ) ) ) {
		return;
	}

	// Save the data.
	if ( isset( $_POST['council_meeting_start_date'] ) && '' !== sanitize_text_field( wp_unslash( $_POST['council_meeting_start_date'] ) ) ) {
		// Sanitize the start date, then parse into a timestamp.
		$start_date = sanitize_text_field( wp_unslash( $_POST['council_meeting_start_date'] ) );
		$start_date = strtotime( $start_date );

		update_post_meta( $post_id, 'council_meeting_start_date', $start_date );
	} elseif ( isset( $_POST['council_meeting_start_date'] ) ) {
		delete_post_meta( $post_id, 'council_meeting_start_date' );
	}

	if ( isset( $_POST['council_meeting_end_date'] ) && '' !== sanitize_text_field( wp_unslash( $_POST['council_meeting_end_date'] ) ) ) {
		// Sanitize the end date, then parse into a timestamp
		// with a manually-added end-of-day time.
		$end_date = sanitize_text_field( wp_unslash( $_POST['council_meeting_end_date'] ) );
		$end_date = strtotime( $end_date . ' 23:59:59' );

		update_post_meta( $post_id, 'council_meeting_end_date', $end_date );
	} elseif ( isset( $_POST['council_meeting_end_date'] ) ) {
		delete_post_meta( $post_id, 'council_meeting_end_date' );
	}

	if ( isset( $_POST['council_meeting_location'] ) && '' !== sanitize_text_field( wp_unslash( $_POST['council_meeting_location'] ) ) ) {
		$location = sanitize_text_field( wp_unslash( $_POST['council_meeting_location'] ) );

		update_post_meta( $post_id, 'council_meeting_location', $location );
	} elseif ( isset( $_POST['council_meeting_location'] ) ) {
		delete_post_meta( $post_id, 'council_meeting_location' );
	}

	if ( isset( $_POST['council_meeting_documents'] ) && is_array( $_POST['council_meeting_documents'] ) ) {
		$docs_in_order   = fixdragdrop_item_order( $_POST['council_meeting_documents'] );
		$store_documents = array();
		foreach ( $docs_in_order as $document ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$title = '';
			$url   = '';

			if ( isset( $document['title'] ) ) {
				$title = sanitize_text_field( $document['title'] );
			}

			if ( isset( $document['url'] ) ) {
				$url = esc_url_raw( $document['url'] );
			}

			$store_documents[] = array(
				'title' => $title,
				'url'   => $url,
			);
		}
		update_post_meta( $post_id, 'council_meeting_documents', $store_documents );
	}
}

/**
 * Create a Sugar Calendar event using data from a Council Meeting post.
 *
 * @param int     $post_id The post ID.
 * @param WP_Post $post    Post object.
 */
function create_event( $post_id, $post ) {

	// Return early if the `sugar_calendar_add_event` function doesn't exist.
	if ( ! function_exists( 'sugar_calendar_add_event' ) ) {
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

	// The `save_post` hook fires as soon as "Add New…" is clicked,
	// and we don't want to create an event post at that time.
	if ( 'Auto Draft' === $post->post_title ) {
		return;
	}

	// Return early if the nonce isn't in place.
	if ( ! isset( $_POST['meeting_meta_fields'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['meeting_meta_fields'] ) ), basename( __FILE__ ) ) ) {
		return;
	}

	// Return early if the event date meta isn't available.
	if (
		! isset( $_POST['council_meeting_start_date'] ) || '' === sanitize_text_field( wp_unslash( $_POST['council_meeting_start_date'] ) )
		|| ! isset( $_POST['council_meeting_end_date'] ) || '' === sanitize_text_field( wp_unslash( $_POST['council_meeting_end_date'] ) )
	) {
		return;
	}

	// Get the Council Meeting post data that we'll be using.
	$permalink = esc_url( get_permalink( $post_id ) );
	$title     = wp_strip_all_tags( $post->post_title );
	$location  = ( isset( $_POST['council_meeting_location'] ) ) ? sanitize_text_field( wp_unslash( $_POST['council_meeting_location'] ) ) : '';
	$start     = sanitize_text_field( wp_unslash( $_POST['council_meeting_start_date'] . ' 00:00:00' ) );
	$end       = sanitize_text_field( wp_unslash( $_POST['council_meeting_end_date'] . ' 00:00:00' ) );

	// Define the data to use for updating or adding the `sc_event` post.
	$sc_event_post = array(
		'post_title'   => $title,
		'post_status'  => $post->post_status,
		'post_content' => $post->post_excerpt,
	);

	// Define the data to use for either updating or adding an event.
	$sc_event_data = array(
		'title'    => $title,
		'status'   => $post->post_status,
		'start'    => $start,
		'end'      => $end,
		'location' => $location,
	);

	// Check for an `sc_event` post that has the permalink as
	// the value of the `_council_meeting_permalink` meta key.
	$sc_event_query = new \WP_Query(
		array(
			'meta_key'               => '_council_meeting_permalink', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'             => $permalink, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'post_type'              => 'sc_event',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		)
	);

	// If an `sc_event` post was found, update its data and then bail.
	if ( $sc_event_query->have_posts() ) {
		while ( $sc_event_query->have_posts() ) {
			$sc_event_query->the_post();
			$event = sugar_calendar_get_event_by_object( get_the_ID() );

			// If an event was found, update its post and data.
			if ( $event ) {

				// Update the `sc_event` post.
				$sc_event_post['ID'] = get_the_ID();
				wp_update_post( $sc_event_post );

				// Update the event data.
				$sc_event_data['object_id'] = get_the_ID();
				sugar_calendar_update_event( $event->id, $sc_event_data );
			}
		}

		return;
	}

	// Create the `sc_event` post using the council meeting data.
	$sc_event = wp_insert_post(
		array_merge(
			$sc_event_post,
			array(
				'post_type'  => 'sc_event',
				'meta_input' => array(
					'_council_meeting_permalink' => $permalink,
				),
			)
		)
	);

	// Return early if the `sc_event` post creation failed.
	if ( ! $sc_event ) {
		return;
	}

	// Merge additional properties before adding event data.
	$sc_event_data = array_merge(
		$sc_event_data,
		array(
			'object_id'      => $sc_event,
			'object_type'    => 'post',
			'object_subtype' => 'sc_event',
			'content'        => '',
			'start_tz'       => '',
			'end_tz'         => '',
			'all_day'        => 1,
		)
	);

	// Add the event data.
	sugar_calendar_add_event( $sc_event_data );

}

/**
 * Delete a Sugar Calendar event if its associated Council Meeting post is trashed.
 *
 * @param int $post_id The post ID.
 */
function delete_event( $post_id ) {

	// Return early if the trashed post is not of the `council_meeting` type.
	if ( 'council_meeting' !== get_post_type( $post_id ) ) {
		return;
	}

	// Check for an `sc_event` post that has the permalink as
	// the value of the `_council_meeting_permalink` meta key.
	$sc_event_query = new \WP_Query(
		array(
			'meta_key'               => '_council_meeting_permalink', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'             => esc_url( get_permalink( $post_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'post_type'              => 'sc_event',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		)
	);

	// If an `sc_event` post was found, delete it and its data.
	if ( $sc_event_query->have_posts() ) {
		while ( $sc_event_query->have_posts() ) {
			$sc_event_query->the_post();

			$event = sugar_calendar_get_event_by_object( get_the_ID() );

			if ( $event ) {
				// Delete the `sc_event` post.
				wp_delete_post( get_the_ID() );

				// Delete the event data.
				sugar_calendar_delete_event( $event->id );
			}
		}
	}
}

/**
 * Redirect Sugar Calendar events generated from Council Meeting posts.
 */
function redirect_events() {

	// Return early if this isn't an `sc_event` post.
	if ( ! is_singular( 'sc_event' ) ) {
		return;
	}

	// Get the current post ID.
	$post_id = get_queried_object_id();

	// Attempt to retrieve a value from the `_council_meeting_permalink` meta key.
	$permalink = get_post_meta( $post_id, '_council_meeting_permalink', true );

	// Return early if the post has no `_council_meeting_permalink` meta.
	if ( ! $permalink ) {
		return;
	}

	// Redirect to the associated Council Meeting post.
	wp_safe_redirect( esc_url( $permalink ), 301 );

	exit;
}

/**
 * Retrieve the current council meeting. If there is no current meeting,
 * retrieve the next upcoming council meeting.
 *
 * @return WP_Post|bool A post object containing the meeting. False if no
 *                      meeting is available.
 */
function get_current_council_meeting() {
	$today = time();

	$current_meeting_args = array(
		'post_type'      => 'council_meeting',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'order'          => 'ASC',
		'meta_key'       => 'council_meeting_start_date', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'orderby'        => 'meta_value_num',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => 'council_meeting_start_date',
				'value'   => $today,
				'compare' => '<=',
			),
			array(
				'key'     => 'council_meeting_end_date',
				'value'   => $today,
				'compare' => '>=',
			),
		),
	);

	$council_meetings = get_posts( $current_meeting_args );

	if ( 0 === count( $council_meetings ) ) {
		$upcoming_meeting_args = $current_meeting_args;

		$upcoming_meeting_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => 'council_meeting_start_date',
				'value'   => $today,
				'compare' => '>',
			),
		);

		$council_meetings = get_posts( $upcoming_meeting_args );
	}

	if ( 0 === count( $council_meetings ) ) {
		return false;
	}

	return $council_meetings[0];
}

/**
 * Retrieve the latest past council meeting.
 *
 * @return WP_Post|bool A post object containing the meeting. False if no
 *                      meeting is available.
 */
function get_past_council_meeting() {
	$today = time();

	$past_meeting_args = array(
		'post_type'      => 'council_meeting',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'order'          => 'DESC',
		'meta_key'       => 'council_meeting_end_date', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'orderby'        => 'meta_value_num',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => 'council_meeting_end_date',
				'value'   => $today,
				'compare' => '<',
			),
		),
	);

	$council_meetings = get_posts( $past_meeting_args );

	if ( 0 === count( $council_meetings ) ) {
		return false;
	}

	return $council_meetings[0];
}

/**
 * Display the front-end output for the `council_meeting` shortcode.
 *
 * @param array $atts A list of attributes passed to the shortcode.
 *
 * @return string HTML output.
 */
function render_council_meeting_shortcode( $atts ) {
	$defaults = array(
		'type' => 'future',
	);

	$atts = wp_parse_args( $atts, $defaults );

	if ( 'future' === $atts['type'] ) {
		$meeting = get_current_council_meeting();
	} elseif ( 'past' === $atts['type'] ) {
		$meeting = get_past_council_meeting();
	} else {
		return ''; // No other types are supported.
	}

	// There is no meeting to return, output an empty string.
	if ( ! $meeting ) {
		return '';
	}

	$start_date  = get_post_meta( $meeting->ID, 'council_meeting_start_date', true );
	$end_date    = get_post_meta( $meeting->ID, 'council_meeting_end_date', true );
	$location    = get_post_meta( $meeting->ID, 'council_meeting_location', true );
	$documents   = get_post_meta( $meeting->ID, 'council_meeting_documents', true );
	$description = get_the_excerpt( $meeting );
	$meeting_url = get_permalink( $meeting->ID );
	$today       = time();

	if ( 'past' === $atts['type'] ) {
		$title = 'Previous Council Meeting';
	} elseif ( 'future' === $atts['type'] ) {
		if ( $start_date <= $today ) {
			$title = 'Current Council Meeting';
		} else {
			$title = 'Upcoming Council Meeting';
		}
	}

	ob_start();
	?>
	<div class="block-box">
		<h2 class="block-box-head"><?php echo esc_html( $title ); ?></h2>
		<div class="council-meeting-box">
			<div class="wp-block-button is-style-arrow-link cm-link"><a href="<?php echo esc_url( $meeting_url ); ?>"><?php echo esc_html( date( 'F', $start_date ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date ?> Council meeting</a></div>

			<datetime class="block-date"><?php echo esc_html( date( 'M j', $start_date ) ) . '&ndash;' . esc_html( date( 'j, Y', $end_date ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date ?></datetime>

			<?php if ( $location ) : ?>
				<p class="block-location"><?php echo esc_html( $location ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $description ) : ?>
				<p class="block-descr"><?php echo wp_kses_post( $description ); ?></p>
			<?php endif; ?>

			<?php

			if ( is_array( $documents ) ) {
				foreach ( $documents as $cnt => $document ) {
					if ( '' === trim( $document['title'] ) || '' === trim( $document['url'] ) ) {
						continue;
					}

					$style_class = 0 === $cnt ? 'is-style-default' : 'is-style-outline';
					?>
						<div class="wp-block-button <?php echo $style_class; // phpcs:ignore ?> meeting-document">
							<a href="<?php echo esc_url( $document['url'] ); ?>" class="wp-block-button__link"><?php echo esc_html( $document['title'] ); ?></a>
						</div>
					<?php

				}
			}

			?>
		</div>
	</div>

	<?php
	$html = ob_get_clean();

	return $html;
}

/**
 * Get past council meetings and populate them.
 */
function show_past_council_meetings() {
	$today = time();

	$past_council_meeting_args = array(
		'post_type'      => 'council_meeting',
		'post_status'    => 'publish',
		'posts_per_page' => 200, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
		'order'          => 'DESC',
		'meta_key'       => 'council_meeting_end_date', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'orderby'        => 'meta_value_num',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => 'council_meeting_end_date',
				'value'   => $today,
				'compare' => '<',
			),
		),
	);

	$all_past_council_meetings = new \WP_Query( $past_council_meeting_args );

	if ( $all_past_council_meetings->have_posts() ) {
		ob_start();
		while ( $all_past_council_meetings->have_posts() ) {
			$all_past_council_meetings->the_post();
			?>
			<article class="card card--council_meeting">
				<h2 class="entry-title"><a href="<?php echo esc_html( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2>

			<?php

			$location = get_post_meta( get_the_ID(), 'council_meeting_location', true );

			if ( $location ) :
				?>
				<p class="block-location"><?php echo esc_html( $location ); ?></p>
				<?php
			endif;

			$documents = get_post_meta( get_the_ID(), 'council_meeting_documents', true );

			if ( is_array( $documents ) ) {
				echo '<ul class="meeting-doc-list">';

				foreach ( $documents as $cnt => $document ) {
					if ( '' === trim( $document['title'] ) || '' === trim( $document['url'] ) ) {
						continue;
					}
					?>
						<li class="meeting-doc"><a href="<?php echo esc_url( $document['url'] ); ?>" class=""><?php echo esc_html( $document['title'] ); ?></a></li>
					<?php
				}
				echo '</ul>';
			}

			?>
			</article>
			<?php
		}
		$html = ob_get_clean();
	} else {
		$html = '';
	}

	wp_reset_postdata();

	return $html;
}

/**
 * Get future council meetings and populate them.
 */
function show_future_council_meetings() {
	$today = time();

	$future_council_meeting_args = array(
		'post_type'      => 'council_meeting',
		'post_status'    => 'publish',
		'posts_per_page' => 200, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
		'order'          => 'ASC',
		'meta_key'       => 'council_meeting_end_date', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'orderby'        => 'meta_value_num',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => 'council_meeting_end_date',
				'value'   => $today,
				'compare' => '>',
			),
		),
	);

	$all_future_council_meetings = new \WP_Query( $future_council_meeting_args );

	if ( $all_future_council_meetings->have_posts() ) {
		ob_start();
		while ( $all_future_council_meetings->have_posts() ) {
			$all_future_council_meetings->the_post();
			?>
			<article class="card card--council_meeting">
				<h2 class="entry-title"><a href="<?php echo esc_html( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2>

			<?php
			$location = get_post_meta( get_the_ID(), 'council_meeting_location', true );

			if ( $location ) :
				?>
				<p class="block-location"><?php echo esc_html( $location ); ?></p>
				<?php
			endif;

			$documents = get_post_meta( get_the_ID(), 'council_meeting_documents', true );

			if ( is_array( $documents ) ) {
				echo '<ul class="meeting-doc-list">';

				foreach ( $documents as $cnt => $document ) {
					if ( '' === trim( $document['title'] ) || '' === trim( $document['url'] ) ) {
						continue;
					}
					?>
						<li class="meeting-doc"><a href="<?php echo esc_url( $document['url'] ); ?>" class=""><?php echo esc_html( $document['title'] ); ?></a></li>
					<?php
				}
				echo '</ul>';
			}

			?>
			</article>
			<?php
		}
		$html = ob_get_clean();
	} else {
		$html = '';
	}

	wp_reset_postdata();

	return $html;
}
