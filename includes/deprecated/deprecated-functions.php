<?php

use Mailchimp\WordPress\Includes\Validation\Validate_Merge_Fields;

// Require here in case this file is loaded before the mailchimp.php plugin entrypoint
// TODO: Remove this after composer autoloading is merged into develop
require_once dirname( __DIR__, 2 ) . '/includes/validation/class-mailchimp-validation.php';

/**
 * Validate phone.
 *
 * This function has been deprecated since version 1.7.0 and will be removed in a future release.
 *
 * @deprecated 1.7.0 Use mailchimp_sf_validate_phone() instead.
 *
 * @param array $opt_val Option value.
 * @param array $data    Data.
 * @return void|WP_Error Returns an error object if validation fails, otherwise processes the value.
 */
function mailchimp_sf_merge_validate_phone( $opt_val, $data ) {
	$validator = new Validate_Merge_Fields();
	return $validator->validate_phone( $opt_val, $data );
}

/**
 * Validate address.
 *
 * This function has been deprecated since version 1.7.0 and will be removed in a future release.
 *
 * @deprecated 1.7.0 Use mailchimp_sf_validate_address() instead.
 *
 * @param array $opt_val Option value.
 * @param array $data    Data.
 * @return void|WP_Error Returns an error object if validation fails, otherwise processes the value.
 */
function mailchimp_sf_merge_validate_address( $opt_val, $data ) {
	$validator = new Validate_Merge_Fields();
	return $validator->validate_address( $opt_val, $data );
}
