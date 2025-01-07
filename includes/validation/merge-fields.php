<?php
/**
 * Merge field validation functions
 *
 * @package mailchimp
 */

namespace Mailchimp\WordPress\Includes\Validation;

use WP_Error;
use stdClass;

/**
 * Validate phone
 *
 * @param array $opt_val Option value
 * @param array $data Data
 * @return array|WP_Error
 */
function merge_validate_phone( $opt_val, $data ) {
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

/**
 * Validate address
 *
 * @param array $opt_val Option value
 * @param array $data Data
 * @return mixed
 */
function mailchimp_sf_merge_validate_address( $opt_val, $data ) {
	if ( 'Y' === $data['required'] ) {
		if ( empty( $opt_val['addr1'] ) || empty( $opt_val['city'] ) ) {
			/* translators: %s: field name */
			$message = sprintf( esc_html__( 'You must fill in %s.', 'mailchimp' ), esc_html( $data['name'] ) );
			$error   = new WP_Error( 'invalid_address_merge', $message );
			return $error;
		}
		/**
		 * Condition
		 * 1) If the address is not required and
		 * 2) address line 1 or city is empty
		 *
		 * Result
		 * Then don't send address to Mailchimp
		 * Return false skips merge formatting in `mailchimp_sf_merge_submit`
		 */
	} elseif ( empty( $opt_val['addr1'] ) || empty( $opt_val['city'] ) ) {
		return false;
	}

	$merge          = new stdClass();
	$merge->addr1   = $opt_val['addr1'];
	$merge->addr2   = $opt_val['addr2'];
	$merge->city    = $opt_val['city'];
	$merge->state   = $opt_val['state'];
	$merge->zip     = $opt_val['zip'];
	$merge->country = $opt_val['country'];
	return $merge;
}
