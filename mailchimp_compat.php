<?php
/**
 * Home for Backwards Compatibility Functions
 *
 * @package Mailchimp
 */

/* Form Display Functions */
if ( ! function_exists( 'mc_display_widget' ) ) {

	/**
	 * Alias for `mailchimp_sf_signup_form`
	 *
	 * @param array $args Signup form args.
	 * @return void
	 */
	function mc_display_widget( $args = array() ) {
		mailchimp_sf_signup_form( $args );
	}
}
if ( ! function_exists( 'mailchimp_sf_display_widget' ) ) {

	/**
	 * Alias for `mailchimp_sf_signup_form`
	 *
	 * @param array $args Signup form args.
	 * @return void
	 */
	function mailchimp_sf_display_widget( $args = array() ) {
		mailchimp_sf_signup_form( $args );
	}
}

/* Shortcodes */
add_shortcode( 'mailchimpsf_widget', 'mailchimp_sf_shortcode' );

/**
 * Deprecates functions for plugin version 1.6.0
 *
 * Changes function names to snake case
 */

/**
 * (Deprecated) Displays a Mailchimp Signup Form
 *
 * @param array $args Args
 * @return void
 */
function mailchimpSF_signup_form( $args = array() ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid -- ignored due to deprecating function
	_deprecated_function( __FUNCTION__, '1.6.0', 'mailchimp_sf_signup_form' );
	mailchimp_sf_signup_form( $args );
}
