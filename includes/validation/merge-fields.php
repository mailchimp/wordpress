<?php
/**
 * Merge field validation functions
 *
 * @package mailchimp
 */

namespace Mailchimp\WordPress\Includes\Validation;

use WP_Error;

/**
 * Validate phone
 *
 * @param array $opt_val Option value
 * @param array $data Data
 * @return array|WP_Error
 */
function mailchimp_sf_merge_validate_phone( $opt_val, $data ) {
	// Filter out falsy values
	$opt_val = array_filter( $opt_val );

	// If they were all empty
	if ( ! $opt_val ) {
		return $opt_val;
	}

	// Trim Whitespace - Beginning and end
	$opt_val = array_map( 'trim', $opt_val );

	// Trim Whitespace - Middle
	$opt_val = array_map(
		function ( $s ) {
			return preg_replace( '/\s/', '', $s );
		},
		$opt_val
	);

	// Format number for validation
	$opt_val = implode( '-', $opt_val );

	switch ( true ) {
		/**
		 * Phone number must be 12 characters long
		 * 10 digits [0-9] and 2 dashes "-"
		 */
		case strlen( $opt_val ) < 12:
			$message = sprintf(
				/* translators: %s: field name */
				esc_html__( '%s must contain the correct amount of digits', 'mailchimp' ),
				esc_html( $data['name'] )
			);
			$opt_val = new WP_Error( 'mc_phone_validation', $message );
			break;

		/**
		 * Phone number must consist of only numbers
		 */
		case ! preg_match( '/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/', $opt_val ):
			$message = sprintf(
				/* translators: %s: field name */
				esc_html__( '%s must consist of only numbers', 'mailchimp' ),
				esc_html( $data['name'] )
			);
			$opt_val = new WP_Error( 'mc_phone_validation', $message );
			break;

		/**
		 * No issues, pass validation
		 */
		default:
			break;
	}

	return $opt_val;
}
