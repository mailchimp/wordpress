<?php
/**
 * Deprecated functions.
 *
 * Where functions come to die.
 *
 * @package Mailchimp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include the needed class incase if it is not already included.
require_once MCSF_DIR . 'includes/class-mailchimp-form-submission.php';

/**
 * Prepare the merge fields body for the API request.
 *
 * @deprecated 1.8.0
 * @param array $merge_fields Merge fields.
 * @return stdClass|WP_Error
 */
function mailchimp_sf_merge_submit( $merge_fields ) {
	_deprecated_function( __FUNCTION__, '1.8.0', 'Mailchimp_Form_Submission::prepare_merge_fields_body()' );

	$form_submission = new Mailchimp_Form_Submission();
	return $form_submission->prepare_merge_fields_body( $merge_fields );
}

/**
 * Prepare the interest groups body for the API request.
 *
 * @deprecated 1.8.0
 * @param array $interest_groups Interest groups.
 * @return stdClass
 */
function mailchimp_sf_groups_submit( $interest_groups ) {
	_deprecated_function( __FUNCTION__, '1.8.0', 'Mailchimp_Form_Submission::prepare_groups_body()' );

	$form_submission = new Mailchimp_Form_Submission();
	return $form_submission->prepare_groups_body( $interest_groups );
}

/**
 * Set all groups to false
 *
 * @deprecated 1.8.0
 * @return StdClass
 */
function mailchimp_sf_set_all_groups_to_false() {
	_deprecated_function( __FUNCTION__, '1.8.0', 'Mailchimp_Form_Submission::set_all_groups_to_false()' );

	$interest_groups = get_option( 'mc_interest_groups' );
	$form_submission = new Mailchimp_Form_Submission();
	return $form_submission->set_all_groups_to_false( $interest_groups );
}

/**
 * Get signup form URL.
 *
 * @deprecated 1.8.0
 * @return string
 */
function mailchimp_sf_signup_form_url() {
	_deprecated_function( __FUNCTION__, '1.8.0', 'Mailchimp_Form_Submission::get_signup_form_url()' );

	$list_id         = get_option( 'mc_list_id' );
	$form_submission = new Mailchimp_Form_Submission();
	return $form_submission->get_signup_form_url( $list_id );
}


/**
 * Attempts to signup a user, per the $_POST args.
 *
 * This sets a global message, that is then used in the widget
 * output to retrieve and display that message.
 *
 * @deprecated 1.8.0
 *
 * @return bool
 */
function mailchimp_sf_signup_submit() {
	_deprecated_function( __FUNCTION__, '1.8.0', 'Mailchimp_Form_Submission::handle_form_submission()' );

	$form_submission = new Mailchimp_Form_Submission();
	$response        = $form_submission->handle_form_submission();

	// If we have an error, then show it.
	if ( is_wp_error( $response ) ) {
		$error = $response->get_error_message();
		mailchimp_sf_global_msg( '<strong class="mc_error_msg">' . $error . '</strong>' );
		return false;
	}

	mailchimp_sf_global_msg( '<strong class="mc_success_msg">' . esc_html( $response ) . '</strong>' );
	return true;
}

/**
 * Remove empty merge fields from the request body.
 *
 * @deprecated 1.8.0
 *
 * @param object $merge Merge fields request body.
 * @return object The modified merge fields request body.
 */
function mailchimp_sf_merge_remove_empty( $merge ) {
	_deprecated_function( __FUNCTION__, '1.8.0', 'Mailchimp_Form_Submission::remove_empty_merge_fields()' );

	$form_submission = new Mailchimp_Form_Submission();
	return $form_submission->remove_empty_merge_fields( $merge );
}
