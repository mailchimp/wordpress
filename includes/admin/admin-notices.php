<?php
/**
 * Admin notices.
 *
 * @package Mailchimp
 */

/**
 * Display success admin notice.
 *
 * NOTE: WordPress localization i18n functionality should be done
 * on string literals outside of this function in order to work
 * correctly.
 *
 * For more info read here: https://salferrarello.com/why-__-needs-a-hardcoded-string-in-wordpress/
 *
 * @since 1.7.0
 * @param string $msg The message to display.
 * @return void
 */
function mailchimp_sf_admin_notice_success( string $msg ): void {
	?>
	<div class="notice notice-success is-dismissible">
		<p>
		<?php
		echo wp_kses(
			$msg,
			array(
				'a'      => array(
					'href'   => array(),
					'title'  => array(),
					'target' => array(),
				),
				'strong' => array(),
				'em'     => array(),
			)
		);
		?>
		</p>
	</div>
	<?php
}

/**
 * Display error admin notice.
 *
 * NOTE: WordPress localization i18n functionality should be done
 * on string literals outside of this function in order to work
 * correctly.
 *
 * For more info read here: https://salferrarello.com/why-__-needs-a-hardcoded-string-in-wordpress/
 *
 * @since 1.7.0
 * @param string $msg The message to display.
 * @return void
 */
function mailchimp_sf_admin_notice_error( string $msg ): void {
	?>
	<div class="notice notice-error">
		<p>
		<?php
		echo wp_kses(
			$msg,
			array(
				'a'      => array(
					'href'   => array(),
					'title'  => array(),
					'target' => array(),
				),
				'strong' => array(),
				'em'     => array(),
			)
		);
		?>
		</p>
	</div>
	<?php
}