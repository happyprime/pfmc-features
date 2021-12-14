<?php
/**
 * Plugin Name:       PFMC Features
 * Plugin URI:        https://github.com/happyprime/pfmc-feature-set
 * GitHub Plugin URI: https://github.com/happyprime/pfmc-feature-set
 * Primary Branch:    release
 * Description:       Custom features for the Pacific Fishery Management Council website.
 * Author:            Happy Prime
 * Author URI:        https://happyprime.co
 * Version:           0.3.0
 *
 * @package     PFMC_Features
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// This plugin, like WordPress, requires PHP 5.6 and higher.
if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
	add_action( 'admin_notices', 'pfmcfs_admin_notice' );

	/**
	 * Display an admin notice if PHP is not 5.6.
	 */
	function pfmcfs_admin_notice() {
		echo '<div class="error"><p>';
		esc_html_e( 'PFMC Feature Set requires PHP 5.6 to function properly. Please upgrade PHP or deactivate the plugin.', 'pfmc-feature-set' );
		echo '</p></div>';
	}

	return;
}

/**
 * Provides a versioned transient key for getting and setting alert data.
 *
 * @return string Current alert transient key.
 */
function get_pfmc_alert_transient_key() {
	return 'pfmc_alert_data_003';
}

require_once __DIR__ . '/includes/managed-fisheries.php';
require_once __DIR__ . '/includes/council-meetings.php';
require_once __DIR__ . '/includes/actions.php';
require_once __DIR__ . '/includes/shadow-taxonomies.php';
require_once __DIR__ . '/includes/sugar-calendar.php';
require_once __DIR__ . '/includes/document-revisions.php';
require_once __DIR__ . '/includes/media.php';
require_once __DIR__ . '/includes/alert.php';
require_once __DIR__ . '/includes/analytics.php';
require_once __DIR__ . '/includes/sticky-cpt.php';
