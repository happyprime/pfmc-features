<?php

namespace PFMCFS\Media;

add_filter( 'mime_types', __NAMESPACE__ . '\xlsm_mime_fix' );
add_filter( 'upload_mimes', __NAMESPACE__ . '\allow_upload_xlsm_mime' );

/**
 * Adjust the mime type expected from xlsm files from application/vnd.ms-excel.sheet.macroEnabled.12,
 * which is in WordPress by default, to application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
 *
 * @param array $mimes A list of known mime types.
 * @return array A modified list of known mime types.
 */
function xlsm_mime_fix( $mimes ) {
	$mimes['xlsm'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
	return $mimes;
}

/**
 * Add application/vnd.openxmlformats-officedocument.spreadsheetml.sheet to the expected list of
 * allowed mime types.
 *
 * @param array $existing_mimes List of existing mime types.
 * @return array Modified list of existing mime types.
 */
function allow_upload_xlsm_mime( $existing_mimes = array() ) {
	$existing_mimes['xlsm'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
	return $existing_mimes;
}
