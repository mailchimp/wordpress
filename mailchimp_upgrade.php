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
