<?php
/**
 * Suggest to login template
 *
 * @package Mailchimp
 */

$button_text = $login_button_text ?? __( 'Log in', 'mailchimp' );
?>
<button id="mailchimp_sf_oauth_connect" class="button mailchimp-sf-button">
	<span class="mailchimp-sf-loading hidden">
		<svg class="animate-spin" width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
		</svg>
	</span>
	<?php echo esc_html( $button_text ); ?>
</button>
<p class="mailchimp-sf-oauth-error error-field" style="display:none;"></p>
<div id="mailchimp-sf-popup-blocked-modal" style="display:none;">
	<p><?php esc_html_e( 'Please allow your browser to show popups for this page.', 'mailchimp' ); ?></p>
</div>
