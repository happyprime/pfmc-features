<?php
/**
 * Plugin Name: PFMC Feature Set
 * Plugin URI:  https://github.com/happyprime/pfmc-feature-set
 * Description: Custom features for the Pacific Fishery Management Council website.
 * Author:      Happy Prime
 * Author URI:  https://happyprime.co
 * Version:     0.1.0
 *
 * @package     PFMC_Feature_Set
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

require_once __DIR__ . '/includes/managed-fisheries.php';
