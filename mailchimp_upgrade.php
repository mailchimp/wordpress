<?php
/**
 * Upgrade routines.
 *
 * @package Mailchimp
 */

/**
 * Check plugin version.
 *
 * @since 1.6.0
 * @return void
 */
function mailchimp_version_check() {
	$db_option = get_option( 'mc_version' );

	if ( MCSF_VER === $db_option ) {
		return;
	}

	if ( false === $db_option || version_compare( '1.6.0', $db_option, '>' ) ) {
		mailchimp_update_1_6_0();
	}

	if ( false === $db_option || version_compare( '1.7.0', $db_option, '>' ) ) {
		mailchimp_update_1_7_0();
	}

	update_option( 'mc_version', MCSF_VER );
}

add_action( 'plugins_loaded', 'mailchimp_version_check' );

/**
 * Version 1.6.0 update routine
 *   - Remove MonkeyRewards checkbox option
 *
 * @return void
 */
function mailchimp_update_1_6_0() {
	delete_option( 'mc_rewards' );
}

/**
 * Version 1.7.0 update routine
 *   - Set "Include?" value to "off" for hidden fields
 *
 * @return void
 */
function mailchimp_update_1_7_0() {
	$form_fields = get_option( 'mc_merge_vars' );

	if ( ! empty( $form_fields ) && is_array( $form_fields ) ) {
		foreach ( $form_fields as $field ) {
			if ( ! $field['required'] && ! $field['public'] ) {
				$option = 'mc_mv_' . $field['tag'];
				// This is a hidden field, so we don't want to include it.
				// We need to set the option to 'off' so that it doesn't show up in the form.
				update_option( $option, 'off' );
			}
		}
	}
}
