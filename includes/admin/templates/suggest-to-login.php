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

		<?php
		// Login button.
		include_once MCSF_DIR . 'includes/admin/templates/login-button.php'; // phpcs:ignore PEAR.Files.IncludingFile.UseRequireOnce
		?>
	</div>
</div>
