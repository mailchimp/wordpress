<?php
/**
 * Template for the user sync page.
 *
 * @package Mailchimp
 */

?>
<div class="mailchimp-sf-user-sync-page">
	<?php
	/**
	 * Render the user sync status, start cta etc...
	 */
	do_action( 'mailchimp_sf_user_sync_before_form' );
	?>
	<form action="options.php" method="post">
		<?php
		settings_fields( 'mailchimp_sf_user_sync_settings' );
		do_settings_sections( 'mailchimp_sf_user_sync_settings' );
		submit_button( __( 'Save User Sync Settings', 'mailchimp' ), 'mailchimp-sf-button mc-submit user-sync-settings-submit', 'mailchimp_sf_user_sync_settings_submit' );
		?>
	</form>

	<?php
	/**
	 * Render the user sync errors.
	 */
	do_action( 'mailchimp_sf_user_sync_after_form' );
	?>
</div>
