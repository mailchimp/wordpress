<?php
/**
 * Displays a signup form.
 *
 * @package Mailchimp
 */

?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	// Check if we should display the form.
	if ( ! mailchimp_sf_should_display_form() ) {
		return;
	}

	$block_instance = $block->parsed_block;

	// Backwards compatibility for old block.
	$inner_blocks = $block_instance['innerBlocks'] ?? [];
	if ( empty( $inner_blocks ) ) {
		mailchimp_sf_signup_form();
		?>
		</div>
		<?php
		return;
	}

	$list_id                    = $attributes['list_id'] ?? '';
	$header                     = $attributes['header'] ?? '';
	$sub_heading                = $attributes['sub_header'] ?? '';
	$is_preview                 = $attributes['is_preview'] ?? false;
	$submit_text                = $attributes['submit_text'] ?? __( 'Subscribe', 'mailchimp' );
	$merge_fields               = get_option( 'mailchimp_sf_merge_fields_' . $list_id );
	$igs                        = get_option( 'mailchimp_sf_interest_groups_' . $list_id );
	$interest_groups_visibility = $attributes['interest_groups_visibility'] ?? array();
	$show_unsubscribe_link      = $attributes['show_unsubscribe_link'] ?? get_option( 'mc_use_unsub_link' ) === 'on';
	$unsubscribe_link_text      = $attributes['unsubscribe_link_text'] ?? __( 'unsubscribe from list', 'mailchimp' );

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
	if ( get_option( 'mc_nuke_all_styles' ) !== '1' ) {
		?>
		<style>
		.widget_mailchimpsf_widget .widget-title {
			line-height: 1.4em;
			margin-bottom: 0.75em;
		}
		.mc_custom_border_hdr,
		#mc_subheader {
			line-height: 1.25em;
			margin-bottom: 18px;
		}
		.mc_merge_var {
			margin-bottom: 1.0em;
		}
		.mc_var_label,
		.mc_interest_label {
			display: block;
			margin-bottom: 0.5em;
		}
		.mc_input {
			-moz-box-sizing: border-box;
			-webkit-box-sizing: border-box;
			box-sizing: border-box;
			padding: 10px 8px;
			width: 100%;
		}
		.mc_input.mc_phone {
			width: auto;
		}
		select.mc_select {
			margin-top: 0.5em;
			padding: 10px 8px;
			width: 100%;
		}
		.mc_address_label {
			margin-top: 1.0em;
			margin-bottom: 0.5em;
			display: block;
		}
		.mc_address_label ~ select {
			padding: 10px 8px;
			width: 100%;
		}
		.mc_list li {
			list-style: none;
			background: none !important;
		}
		.mc_interests_header {
			margin-top: 1.0em;
			margin-bottom: 0.5em;
		}
		.mc_interest label,
		.mc_interest input {
			margin-bottom: 0.4em;
		}
		#mc_signup_submit {
			margin-top: 1.5em;
			padding: 10px 8px;
			width: 80%;
		}
		#mc_unsub_link a {
			font-size: 0.75em;
		}
		#mc_unsub_link {
			margin-top: 1.0em;
		}
		.mc_header_address,
		.mc_email_format {
			display: block;
			font-weight: bold;
			margin-top: 1.0em;
			margin-bottom: 0.5em;
		}
		.mc_email_options {
			margin-top: 0.5em;
		}
		.mc_email_type {
			padding-left: 4px;
		}
		</style>
		<?php
	}
	?>
	<div class="mc_container">
		<?php
		// See if we have custom header content
		if ( ! empty( $header ) && ! $is_preview ) {
			?>
			<h2>
				<?php echo wp_kses_post( $header ); ?>
			</h2>
			<?php
		}
		?>
		<div id="mc_signup">
			<?php
			if ( $sub_heading && ! $is_preview ) {
				?>
				<div id="mc_subheader">
					<h3>
						<?php echo wp_kses_post( $sub_heading ); ?>
					</h3>
				</div><!-- /mc_subheader -->
				<?php
			}
			?>
			<form method="post" action="#mc_signup" id="mc_signup_form">
				<input type="hidden" id="mc_submit_type" name="mc_submit_type" value="html" />
				<input type="hidden" name="mcsf_action" value="mc_submit_signup_form" />
				<?php wp_nonce_field( 'mc_submit_signup_form', '_mc_submit_signup_form_nonce', false ); ?>
				<div class="mc_form_inside">
					<div class="updated" id="mc_message">
						<?php echo wp_kses_post( mailchimp_sf_global_msg() ); ?>
					</div>

					<?php
					echo $content;

					$visible_inner_blocks = array_filter(
						$inner_blocks,
						function( $inner_block ) {
							return $inner_block['attrs']['visible'] ?? false;
						}
					);

					// Show an explanation of the * if there's more than one field
					if ( count( $visible_inner_blocks ) > 1 ) {
						?>
						<div id="mc-indicates-required">
							* = <?php esc_html_e( 'required field', 'mailchimp' ); ?>
						</div><!-- /mc-indicates-required -->
						<?php
					}

					// Show our Interest groups fields if we have them, and they're set to on
					if ( is_array( $igs ) && ! empty( $igs ) ) {
						foreach ( $igs as $ig ) {
							if ( is_array( $ig ) && isset( $ig['id'] ) ) {
								if ( ( $igs && isset( $interest_groups_visibility[ $ig['id'] ] ) && 'on' === $interest_groups_visibility[ $ig['id'] ] ) ) {
									if ( 'hidden' !== $ig['type'] ) {
										?>
										<div class="mc_interests_header">
											<?php echo esc_html( $ig['title'] ); ?>
										</div><!-- /mc_interests_header -->
										<div class="mc_interest">
										<?php
									} else {
										?>
										<div class="mc_interest" style="display: none;">
										<?php
									}
									?>

									<?php
									mailchimp_interest_group_field( $ig );
									?>
									</div><!-- /mc_interest -->
									<?php
								}
							}
						}
					}

					// TODO: This should be based on selected list in block settings.
					if ( get_option( 'mc_email_type_option' ) ) {
						?>
						<div class="mergeRow">
							<label class="mc_email_format"><?php esc_html_e( 'Preferred Format', 'mailchimp' ); ?></label>
							<div class="field-group groups mc_email_options">
								<ul class="mc_list">
									<li><input type="radio" name="email_type" id="email_type_html" value="html" checked="checked"><label for="email_type_html" class="mc_email_type"><?php esc_html_e( 'HTML', 'mailchimp' ); ?></label></li>
									<li><input type="radio" name="email_type" id="email_type_text" value="text"><label for="email_type_text" class="mc_email_type"><?php esc_html_e( 'Text', 'mailchimp' ); ?></label></li>
								</ul>
							</div>
						</div>

						<?php
					}

					if ( ! $is_preview ) {
						?>
						<div class="mc_signup_submit">
							<input type="submit" name="mc_signup_submit" id="mc_signup_submit" value="<?php echo esc_attr( $submit_text ); ?>" class="button" />
						</div><!-- /mc_signup_submit -->

						<?php
						$user = get_option( 'mc_user' );
						if ( $user && $show_unsubscribe_link ) {
							$api  = mailchimp_sf_get_api();
							$host = 'http://' . $api->datacenter . '.list-manage.com';
							?>
							<div id="mc_unsub_link" align="center">
								<a href="<?php echo esc_url( $host . '/unsubscribe/?u=' . $user['account_id'] . '&amp;id=' . $list_id ); ?>" target="_blank"><?php esc_html( $unsubscribe_link_text ); ?></a>
							</div><!-- /mc_unsub_link -->
							<?php
						}
					}
					?>
				</div><!-- /mc_form_inside -->
			</form><!-- /mc_signup_form -->
		</div><!-- /mc_signup_container -->
	</div><!-- /mc_container -->
</div>
