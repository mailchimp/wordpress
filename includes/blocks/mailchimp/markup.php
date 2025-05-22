<?php
/**
 * Displays a signup form.
 *
 * @package Mailchimp
 */

// Check if we should display the form.
if ( ! mailchimp_sf_should_display_form() ) {
	return;
}

?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php

	// Backwards compatibility for old block, which didn't have innerBlocks.
	$block_instance = $block->parsed_block;
	$inner_blocks   = $block_instance['innerBlocks'] ?? [];
	if ( empty( $inner_blocks ) ) {
		mailchimp_sf_signup_form();
		?>
		</div>
		<?php
		return;
	}

	// Make sure we have a list ID and it's valid.
	$list_id  = $attributes['list_id'] ?? '';
	$lists    = ( new Mailchimp_List_Subscribe_Form_Blocks() )->get_lists();
	$list_ids = array_map(
		function ( $single_list ) {
			return $single_list['id'];
		},
		$lists
	);

	if ( ! in_array( $list_id, $list_ids, true ) ) {
		return;
	}

	$header                      = $attributes['header'] ?? '';
	$sub_heading                 = $attributes['sub_header'] ?? '';
	$submit_text                 = $attributes['submit_text'] ?? __( 'Subscribe', 'mailchimp' );
	$merge_fields                = get_option( 'mailchimp_sf_merge_fields_' . $list_id );
	$show_unsubscribe_link       = $attributes['show_unsubscribe_link'] ?? get_option( 'mc_use_unsub_link' ) === 'on';
	$unsubscribe_link_text       = $attributes['unsubscribe_link_text'] ?? __( 'unsubscribe from list', 'mailchimp' );
	$update_existing_subscribers = ( $attributes['update_existing_subscribers'] ?? get_option( 'mc_update_existing' ) === 'on' ) ? 'yes' : 'no';
	$double_opt_in               = ( $attributes['double_opt_in'] ?? get_option( 'mc_double_optin' ) === 'on' ) ? 'yes' : 'no';
	$show_required_indicator     = $attributes['show_required_indicator'] ?? true;
	$required_indicator_text     = $attributes['required_indicator_text'] ?? __( '* = required field', 'mailchimp' );
	$hash                        = wp_hash(
		serialize( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
			array(
				'list_id'         => $list_id,
				'update_existing' => $update_existing_subscribers,
				'double_opt_in'   => $double_opt_in,
			)
		)
	);

	// See if we have valid Merge Vars
	if ( ! is_array( $merge_fields ) || empty( $list_id ) ) {
		?>
		<div class="mc_container">
			<div class="mc_error_msg">
				<?php
				echo wp_kses(
					__(
						'Sorry, there was a problem loading your Mailchimp details. Please navigate to <strong>Settings</strong> and click <strong>Mailchimp Setup</strong> to try again.',
						'mailchimp'
					),
					[
						'strong' => [],
					]
				);
				?>
			</div>
		</div>
		<?php
		return;
	}
	?>
	<div class="mc_container">
		<?php
		// See if we have custom header content
		if ( ! empty( $header ) ) {
			?>
			<h2 class="mc_custom_border_hdr">
				<?php echo wp_kses_post( $header ); ?>
			</h2>
			<?php
		}
		if ( $sub_heading ) {
			?>
			<div id="mc_subheader">
				<h3>
					<?php echo wp_kses_post( $sub_heading ); ?>
				</h3>
			</div><!-- /mc_subheader -->
			<?php
		}
		?>
		<div id="mc_signup">
			<form method="post" action="#mc_signup" id="mc_signup_form" class="mc_signup_form">
				<input type="hidden" id="mc_submit_type" class="mc_submit_type" name="mc_submit_type" value="html" />
				<input type="hidden" name="mcsf_action" value="mc_submit_signup_form" />
				<input type="hidden" name="mailchimp_sf_list_id" value="<?php echo esc_attr( $list_id ); ?>" />
				<input type="hidden" name="mailchimp_sf_update_existing_subscribers" value="<?php echo esc_attr( $update_existing_subscribers ); ?>" />
				<input type="hidden" name="mailchimp_sf_double_opt_in" value="<?php echo esc_attr( $double_opt_in ); ?>" />
				<input type="hidden" name="mailchimp_sf_hash" value="<?php echo esc_attr( $hash ); ?>" />
				<?php
				wp_nonce_field( 'mc_submit_signup_form', '_mc_submit_signup_form_nonce', false );
				?>
				<div class="mc_form_inside">
					<div class="mc_message_wrapper" id="mc_message">
						<?php echo wp_kses_post( mailchimp_sf_global_msg() ); ?>
					</div>

					<?php
					/**
					 * the $content is the html generated from innerBlocks
					 * it is being created from the save method in JS or the render_callback
					 * in php and is sanitized.
					 *
					 * Re sanitizing it through `wp_kses_post` causes
					 * embed blocks to break and other core filters don't apply.
					 * therefore no additional sanitization is done and it is being output as is
					 */
					echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

					// Show an explanation of the * if there's more than one field
					if ( $show_required_indicator ) {
						?>
						<div id="mc-indicates-required">
							<?php echo esc_html( $required_indicator_text ); ?>
						</div><!-- /mc-indicates-required -->
						<?php
					}

					// Add a honeypot field.
					mailchimp_sf_honeypot_field();
					?>
					<div class="mc_signup_submit">
						<input type="submit" name="mc_signup_submit" class="mc_signup_submit_button" id="mc_signup_submit" value="<?php echo esc_attr( $submit_text ); ?>" class="button" />
					</div><!-- /mc_signup_submit -->

					<?php
					$user = get_option( 'mc_user' );
					if ( $user && $show_unsubscribe_link ) {
						$api  = mailchimp_sf_get_api();
						$host = 'https://' . $api->datacenter . '.list-manage.com';
						?>
						<div id="mc_unsub_link" align="center">
							<a href="<?php echo esc_url( $host . '/unsubscribe/?u=' . $user['account_id'] . '&amp;id=' . $list_id ); ?>" target="_blank">
								<?php echo esc_html( $unsubscribe_link_text ); ?>
							</a>
						</div><!-- /mc_unsub_link -->
						<?php
					}
					?>
				</div><!-- /mc_form_inside -->
			</form><!-- /mc_signup_form -->
		</div><!-- /mc_signup_container -->
	</div><!-- /mc_container -->
</div>
