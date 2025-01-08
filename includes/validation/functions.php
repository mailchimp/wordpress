<?php
/**
 * Functions for validation
 *
 * Here for backwards compatibility or until we have a better idea
 * how to refactor the client code.
 *
 * The Validate_Merge_Fields that contains the validation logic
 * was created for ability to accurately and easily unit test
 * the validation.
 *
 * @package mailchimp
 */

namespace Mailchimp\WordPress\Includes\Validation;

use Mailchimp\WordPress\Includes\Validation\Validate_Merge_Fields;

/**
 * Expose merge_validate_phone function to maintain backward compatibility.
 * TODO: Introduce a DI Container (PHP DI) to manage dependency injection.
 *
 * @param array $opt_val array of input from user
 * @param array $data other form data needed for error message
 */
function merge_validate_phone( $opt_val, $data ) {
	$validator = new Validate_Merge_Fields();
	return $validator->validate_phone( $opt_val, $data );
}

/**
 * Expose merge_validate_address function to maintain backward compatibility.
 * TODO: Introduce a DI Container (PHP DI) to manage dependency injection.
 *
 * @param array $opt_val array of input from user
 * @param array $data other form data needed for error message
 */
function merge_validate_address( $opt_val, $data ) {
	$validator = new Validate_Merge_Fields();
	return $validator->validate_address( $opt_val, $data );
}
