<?php
/**
 * Settings page header template
 *
 * @package Mailchimp
 */

?>
<div class="mailchimp-sf-settings-page-hero-wrapper">
	<div class="mailchimp-sf-settings-page-hero">
		<div class="mailchimp-sf-settings-page-hero-title-wrapper">
			<h1 class="mailchimp-sf-settings-page-hero-title">
				<?php esc_html_e( 'Settings', 'mailchimp' ); ?>
			</h1>
			<p class="mailchimp-sf-settings-page-hero-description">
				<?php esc_html_e( 'You can use this page to configure the default fields, copy, and behavior of the Mailchimp block.', 'mailchimp' ); ?>
			</p>
		</div>
		<div class="mailchimp-sf-settings-page-hero-content-wrapper">
			<div class="mailchimp-sf-settings-page-hero-content">
				<h3>
					<?php esc_html_e( 'How to use your form', 'mailchimp' ); ?>
				</h3>
				<p>
					<?php esc_html_e( 'You can now find your forms in the editor, select the + icon and look for your form under "Mailchimp List Subscribe Form".', 'mailchimp' ); ?>
				</p>
			</div>
			<div class="mailchimp-sf-settings-page-hero-content-image">
				<img src="<?php echo esc_url( MCSF_URL . 'assets/images/settings-block.png' ); ?>" alt="Settings Hero">
			</div>
		</div>
	</div>
</div>
