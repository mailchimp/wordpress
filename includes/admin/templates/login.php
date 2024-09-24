<?php
/**
 * Login template
 *
 * @package Mailchimp
 */

?>
<div class="wrap">
	<hr class="wp-header-end" style="display:none;"/>
	<div class="mailchimp-sf-login-content mailchimp-sf-content-box-wrapper">
		<div class="mailchimp-sf-content-box">
			<div class="title"><?php esc_html_e( 'Let\'s connect your Mailchimp account', 'mailchimp' ); ?></div>
			<p class="h4">
				<?php
				esc_html_e( 'Log in to your Mailchimp account or create a new account to authorize and connect to WordPress. Setup should take just a few minutes.', 'mailchimp' );
				?>
			</p>

			<div class="mailchimp-sf-oauth-connect-wrapper">
				<button id="mailchimp_sf_oauth_connect" class="button mailchimp-sf-button">
					<span class="mailchimp-sf-loading hidden">
						<svg class="animate-spin" width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
								<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
								<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
						</svg>
					</span>
					<?php esc_html_e( 'Log in', 'mailchimp' ); ?>
				</button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mailchimp_sf_create_account' ) ); ?>" class="button mailchimp-sf-button button-secondary"><?php esc_html_e( 'Create an account', 'mailchimp' ); ?></a>
			</div>
			<p class="mailchimp-sf-oauth-error error-field" style="display:none;"></p>
			<div id="mailchimp-sf-popup-blocked-modal" style="display:none;">
				<p><?php esc_html_e( 'Please allow your browser to show popups for this page.', 'mailchimp' ); ?></p>
			</div>
		</div>
	</div>
</div>
