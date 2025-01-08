<?php
/**
 * Mailchimp_Validation class
 *
 * @package mailchimp
 */

namespace Mailchimp\WordPress\Includes\Validation;

/**
 * Mailchimp_Validation class
 *
 * Entrypoint for validation functions
 */
class Mailchimp_Validation {

	/**
	 * Initialize the class
	 *
	 * @return void
	 */
	public function init() {
		$this->require_validation_functions();
	}

	/**
	 * Require validation functions
	 *
	 * TODO: Refactor this once autoloading is enabled
	 *
	 * @return void
	 */
	private function require_validation_functions() {
		include_once MCSF_DIR . 'includes/validation/class-validate-merge-fields.php';
		include_once MCSF_DIR . 'includes/validation/functions.php';
	}
}
