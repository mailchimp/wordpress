<?php
/**
 * Deprecated functions for Mailchimp validation.
 *
 * This file contains deprecated validation functions for the Mailchimp WordPress plugin.
 * These functions are kept for backward compatibility and will be removed in a future release.
 *
 * @package mailchimp
 * @deprecated 1.7.0 Deprecated in favor of new validation methods.
 *
 * @note This file is included as a fallback in case it is loaded before the main plugin entrypoint.
 *       It will be obsolete once composer autoloading is implemented.
 *
 * @see \Mailchimp\WordPress\Includes\Validation\Validate_Merge_Fields
 */

use Mailchimp\WordPress\Includes\Validation\Validate_Merge_Fields;
use Mailchimp\WordPress\Includes\Utility\Mailchimp_Location_Detector;

// Require here in case this file is loaded before the mailchimp.php plugin entrypoint
// TODO: Remove this after composer autoloading is merged into develop
require_once dirname( __DIR__, 2 ) . '/includes/validation/class-mailchimp-validation.php';
require_once dirname( __DIR__, 2 ) . '/includes/utility/class-mailchimp-location-detector.php';

/**
 * Validate phone.
 *
 * This function validates phone numbers but has been deprecated since version 1.7.0.
 * Use the Mailchimp\WordPress\Includes\Validation\Validate_Merge_Fields::validate_phone method instead.
 *
 * @deprecated 1.7.0 Use Mailchimp\WordPress\Includes\Validation\Validate_Merge_Fields::validate_phone.
 *
 * @param array $opt_val Option value to validate.
 * @param array $data    Additional data required for validation.
 * @return void|WP_Error Returns an error object if validation fails, otherwise processes the value.
 */
function mailchimp_sf_merge_validate_phone( $opt_val, $data ) {
	_deprecated_function( __FUNCTION__, '1.6.2', 'Validate_Merge_Fields::validate_phone' );
	$validator = new Validate_Merge_Fields();
	return $validator->validate_phone( $opt_val, $data );
}

/**
 * Validate address.
 *
 * This function validates addresses but has been deprecated since version 1.7.0.
 * Use the Mailchimp\WordPress\Includes\Validation\Validate_Merge_Fields::validate_address method instead.
 *
 * @deprecated 1.7.0 Use Mailchimp\WordPress\Includes\Validation\Validate_Merge_Fields::validate_address.
 *
 * @param array $opt_val Option value to validate.
 * @param array $data    Additional data required for validation.
 * @return void|WP_Error Returns an error object if validation fails, otherwise processes the value.
 */
function mailchimp_sf_merge_validate_address( $opt_val, $data ) {
	_deprecated_function( __FUNCTION__, '1.6.2', 'Validate_Merge_Fields::validate_address' );
	$validator = new Validate_Merge_Fields();
	return $validator->validate_address( $opt_val, $data );
}

/**
 * Initialize location detection.
 *
 * This function initializes the Mailchimp location detection logic but has been deprecated
 * since version 1.6.2. Use the Mailchimp_Location_Detector::init method instead.
 *
 * @deprecated 1.6.2 Use Mailchimp_Location_Detector::init.
 *
 * @return void
 */
function mailchimp_sf_where_am_i() {
	_deprecated_function( __FUNCTION__, '1.6.2', 'Mailchimp_Location_Detector::init' );
	$plugin_root_path            = dirname( dirname( __DIR__ ) ) . '/mailchimp.php';
	$mailchimp_location_detector = new Mailchimp_Location_Detector( $plugin_root_path );
	$mailchimp_location_detector->init();
}
