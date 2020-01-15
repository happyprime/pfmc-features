<?php
/**
 * Handling for the Council Meeting post type.
 *
 * @package PFMC_Feature_Set
 */

namespace PFMCFS\Post_Type\Council_Meetings;

add_action( 'init', __NAMESPACE__ . '\register_post_type', 10, 1 );
add_action( 'save_post', __NAMESPACE__ . '\save_council_meeting_meta', 1 );
add_shortcode( 'council_meeting', __NAMESPACE__ . '\render_council_meeting_shortcode', 10 );

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
		$meeting_start_date = date( 'Y-m-d', $meeting_start_date );
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
		$meeting_end_date = date( 'Y-m-d', $meeting_end_date );
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

	if ( ! is_array( $documents ) ) {
		$documents = array();
	}

	while ( $count < 5 ) {
		if ( isset( $documents[ $count ] ) ) {
			$document_title = $documents[ $count ]['title'];
			$document_url   = $documents[ $count ]['url'];
		} else {
			$document_title = '';
			$document_url   = '';
		}

		?>
		<h3>Document <?php echo absint( $count + 1 ); ?></h3>
		<label>Title: <input type="text" name="council_meeting_documents[<?php echo absint( $count ); ?>][title]" value="<?php echo esc_attr( $document_title ); ?>" /></label><br />
		<label>URL: <input type="text" name="council_meeting_documents[<?php echo absint( $count ); ?>][url]" value="<?php echo esc_attr( $document_url ); ?>" /></label>
		<?php

		$count++;
	}
}

/**
 * Save Council Meeting CPT metabox data
 *
 * @param int $post_id The ID of the post.
 */
function save_council_meeting_meta( $post_id ) {
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
		update_post_meta( $post_id, 'council_meeting_start_date', strtotime( $_POST['council_meeting_start_date'] ) ); // phpcs:ignore
	} elseif ( isset( $_POST['council_meeting_start_date'] ) ) {
		delete_post_meta( $post_id, 'council_meeting_start_date' );
	}

	if ( isset( $_POST['council_meeting_end_date'] ) && '' !== sanitize_text_field( wp_unslash( $_POST['council_meeting_end_date'] ) ) ) {
		update_post_meta($post_id, 'council_meeting_end_date', strtotime( $_POST['council_meeting_end_date'] ) ); // phpcs:ignore
	} elseif ( isset( $_POST['council_meeting_end_date'] ) ) {
		delete_post_meta( $post_id, 'council_meeting_end_date' );
	}

	if ( isset( $_POST['council_meeting_location'] ) && '' !== sanitize_text_field( wp_unslash( $_POST['council_meeting_location'] ) ) ) {
		update_post_meta( $post_id, 'council_meeting_location', sanitize_text_field( wp_unslash( $_POST['council_meeting_location'] ) ) );
	} elseif ( isset( $_POST['council_meeting_location'] ) ) {
		delete_post_meta( $post_id, 'council_meeting_location' );
	}

	if ( isset( $_POST['council_meeting_documents'] ) && is_array( $_POST['council_meeting_documents'] ) ) {
		$store_documents = array();
		foreach ($_POST['council_meeting_documents'] as $document) { // phpcs:ignore
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
		'meta_key'       => 'council_meeting_start_date', // Key to order by.
		'orderby'        => 'meta_value_num',
		'meta_query'     => array(
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

		$upcoming_meeting_args['meta_query'] = array(
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
		'meta_key'       => 'council_meeting_end_date', // Key to order by.
		'orderby'        => 'meta_value_num',
		'meta_query'     => array(
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
			<div class="wp-block-button is-style-arrow-link cm-link"><a href="<?php echo esc_url( $meeting_url ); ?>"><?php echo esc_html( date( 'F', $start_date ) ); ?> council meeting</a></div>

			<datetime class="block-date"><?php echo esc_html( date( 'M j', $start_date ) ) . '&ndash;' . esc_html( date( 'j, Y', $end_date ) ); ?></datetime>

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
