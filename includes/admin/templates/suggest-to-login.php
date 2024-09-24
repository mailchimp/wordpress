<?php
/**
 * Suggest to login template
 *
 * @package Mailchimp
 */

?>
<div class="mailchimp-sf-suggest-to-login mailchimp-sf-content-box-wrapper hidden">
	<div class="mailchimp-sf-content-box">
		<div class="title"><?php esc_html_e( 'Login', 'mailchimp' ); ?></div>
		<p class="h4">
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %s - Username */
					__( 'It seems an account already exists with this email. Please try logging in with this username: %s', 'mailchimp' ),
					'<span class="mailchimp-sf-email">' . esc_html( $email ) . '</span>'
				),
				array(
					'span' => array(
						'class' => array(),
					),
				)
			);
			?>
		</p>

		<button id="mailchimp_sf_oauth_connect" class="button mailchimp-sf-button">
			<span class="mailchimp-sf-loading hidden">
				<svg class="animate-spin" width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
			</span>
			<?php esc_html_e( 'Log in', 'mailchimp' ); ?>
		</button>
		<p class="mailchimp-sf-oauth-error error-field" style="display:none;"></p>
		<div id="mailchimp-sf-popup-blocked-modal" style="display:none;">
			<p><?php esc_html_e( 'Please allow your browser to show popups for this page.', 'mailchimp' ); ?></p>
		</div>
	</div>
</div>
