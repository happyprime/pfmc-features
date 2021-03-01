<?php

namespace PFMCFS\Media;

add_filter( 'mime_types', __NAMESPACE__ . '\filter_mime_types' );
add_filter( 'upload_mimes', __NAMESPACE__ . '\filter_upload_mimes' );

/**
 * Adjust the mime type expected from xlsm files from application/vnd.ms-excel.sheet.macroEnabled.12,
 * which is in WordPress by default, to application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
 *
 * @param array $mimes A list of known mime types.
 * @return array A modified list of known mime types.
 */
function filter_mime_types( $mimes ) {
	$mimes['xlsm'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
	$mimes['ppsx'] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
	return $mimes;
}

/**
 * Add application/vnd.openxmlformats-officedocument.spreadsheetml.sheet to the expected list of
 * allowed mime types.
 *
 * @param array $existing_mimes List of existing mime types.
 * @return array Modified list of existing mime types.
 */
function filter_upload_mimes( $existing_mimes ) {
	$existing_mimes['xlsm'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
	$existing_mimes['ppsx'] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';

	return $existing_mimes;
}
