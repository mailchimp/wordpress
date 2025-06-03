<?php
/**
 * View file for the setup page.
 *
 * @package Mailchimp
 */

?>
<div>
	<form method="post" action="<?php echo esc_url( add_query_arg( array( 'page' => 'mailchimp_sf_options' ), admin_url( 'admin.php' ) ) ); ?>">
		<div class="mc-section">
			<input type="hidden" name="mcsf_action" value="change_form_settings">
			<?php wp_nonce_field( 'update_general_form_settings', '_mcsf_nonce_action' ); ?>

			<table class="widefat mc-widefat mc-label-options">
				<tr><th colspan="2"><?php esc_html_e( 'Content Options', 'mailchimp' ); ?></th></tr>
				<tr valign="top">
					<th scope="row">
						<label for="mc_header_content"><?php esc_html_e( 'Header', 'mailchimp' ); ?></label>
					</th>
					<td>
						<textarea class="widefat" id="mc_header_content" name="mc_header_content" rows="2"><?php echo wp_kses_post( get_option( 'mc_header_content' ) ); ?></textarea><br/>
						<?php esc_html_e( 'Add your own text, HTML markup (including image links), or keep it blank.', 'mailchimp' ); ?>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="mc_subheader_content"><?php esc_html_e( 'Sub-header', 'mailchimp' ); ?></label>
					</th>
					<td>
						<textarea class="widefat" id="mc_subheader_content" name="mc_subheader_content" rows="2"><?php echo wp_kses_post( get_option( 'mc_subheader_content' ) ); ?></textarea><br/>
						<?php esc_html_e( 'Add your own text, HTML markup (including image links), or keep it blank.', 'mailchimp' ); ?><br/>
						<?php esc_html_e( 'This will be displayed under the heading and above the form.', 'mailchimp' ); ?>
					</td>
				</tr>

				<tr valign="top" class="last-row">
					<th scope="row">
						<label for="mc_submit_text"><?php esc_html_e( 'Submit Button', 'mailchimp' ); ?></label>
					</th>
					<td>
						<input class="widefat" type="text" id="mc_submit_text" name="mc_submit_text" size="70" value="<?php echo esc_attr( get_option( 'mc_submit_text' ) ); ?>"/>
					</td>
				</tr>
			</table>
			<input type="submit" value="<?php esc_attr_e( 'Update Subscribe Form Settings', 'mailchimp' ); ?>" class="button mailchimp-sf-button small mc-submit" /><br/>

			<?php
			if ( get_option( 'mc_nuke_all_styles' ) === '1' ) {
				?>
				<table class="widefat mc-widefat mc-nuke-styling">
					<tr><th colspan="2"><?php esc_html_e( 'Remove Mailchimp CSS', 'mailchimp' ); ?></th></tr>
					<tr><th><label for="mc_nuke_all_styles"><?php esc_html_e( 'Remove CSS' ); ?></label></th><td><span class="mc-pre-input"></span><input type="checkbox" name="mc_nuke_all_styles" id="mc_nuke_all_styles" <?php checked( get_option( 'mc_nuke_all_styles' ), true ); ?> onclick="showMe('mc-custom-styling')"/><?php esc_html_e( 'This will disable all Mailchimp CSS, so it\'s recommended for WordPress experts only.' ); ?></td></tr>
				</table>
				<?php
			}

			if ( 'on' === get_option( 'mc_custom_style' ) ) {
				?>
				<table class="widefat mc-widefat mc-custom-styling" id="mc-custom-styling" style="<?php echo esc_attr( ( get_option( 'mc_nuke_all_styles' ) === '1' ? 'display:none;' : '' ) ); ?>">
					<tr>
						<th colspan="2"><?php esc_html_e( 'Custom Styling', 'mailchimp' ); ?></th>
					</tr>
					<tr>
						<th>
							<label for="mc_custom_style"><?php esc_html_e( 'Enabled?', 'mailchimp' ); ?></label>
						</th>
						<td>
							<span class="mc-pre-input"></span>
							<input type="checkbox" name="mc_custom_style" id="mc_custom_style"<?php checked( get_option( 'mc_custom_style' ), 'on' ); ?> />
							<em><?php esc_html_e( 'Edit the default Mailchimp CSS style.' ); ?></em>
						</td>
					</tr>
					<tr>
						<th>
							<label for="mc_form_border_width"><?php esc_html_e( 'Border Width (px)', 'mailchimp' ); ?></label>
						</th>
						<td>
							<input type="text" id="mc_form_border_width" name="mc_form_border_width" size="3" maxlength="3" value="<?php echo esc_attr( get_option( 'mc_form_border_width' ) ); ?>"/>
							<em><?php esc_html_e( 'Set to 0 for no border, do not enter', 'mailchimp' ); ?> px</em>
						</td>
					</tr>
					<tr>
						<th>
							<label for="mc_form_border_color"><?php esc_html_e( 'Border Color', 'mailchimp' ); ?></label>
						</th>
						<td>
							<span class="mc-pre-input">#</span>
							<input type="text" id="mc_form_border_color" name="mc_form_border_color" size="7" maxlength="6" value="<?php echo esc_attr( get_option( 'mc_form_border_color' ) ); ?>"/>
							<em><?php esc_html_e( 'Do not enter initial', 'mailchimp' ); ?> <strong>#</strong></em>
						</td>
					</tr>
					<tr>
						<th>
							<label for="mc_form_text_color"><?php esc_html_e( 'Text Color', 'mailchimp' ); ?></label>
						</th>
						<td>
							<span class="mc-pre-input">#</span>
							<input type="text" id="mc_form_text_color" name="mc_form_text_color" size="7" maxlength="6" value="<?php echo esc_attr( get_option( 'mc_form_text_color' ) ); ?>"/>
							<em><?php esc_html_e( 'Do not enter initial', 'mailchimp' ); ?> <strong>#</strong></em>
						</td>
					</tr>
					<tr class="last-row">
						<th>
							<label for="mc_form_background"><?php esc_html_e( 'Background Color', 'mailchimp' ); ?></label>
						</th>
						<td>
							<span class="mc-pre-input">#</span>
							<input type="text" id="mc_form_background" name="mc_form_background" size="7" maxlength="6" value="<?php echo esc_attr( get_option( 'mc_form_background' ) ); ?>"/>
							<em><?php esc_html_e( 'Do not enter initial', 'mailchimp' ); ?> <strong>#</strong></em>
						</td>
					</tr>
				</table>
				<input type="submit" value="<?php esc_attr_e( 'Update Subscribe Form Settings', 'mailchimp' ); ?>" class="button mailchimp-sf-button small mc-submit" /><br/>
				<?php
			}
			?>

			<table class="widefat mc-widefat">
				<tr><th colspan="2"><?php esc_html_e( 'List Options', 'mailchimp' ); ?></th></tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Use Double Opt-In (Recommended)?', 'mailchimp' ); ?></th>
					<td><input name="mc_double_optin" type="checkbox" <?php checked( get_option( 'mc_double_optin' ), true ); ?> id="mc_double_optin" class="code" />
						<em><label for="mc_double_optin"><?php esc_html_e( 'Before new your subscribers are added via the plugin, they\'ll need to confirm their email address.', 'mailchimp' ); ?></label></em>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Update existing subscribers?', 'mailchimp' ); ?></th>
					<td><input name="mc_update_existing" type="checkbox" <?php checked( get_option( 'mc_update_existing' ), true ); ?> id="mc_update_existing" class="code" />
						<em><label for="mc_update_existing"><?php esc_html_e( 'If an existing subscriber fills out this form, we will update their information with what\'s provided.', 'mailchimp' ); ?></label></em>
					</td>
				</tr>

				<tr valign="top" class="last-row">
					<th scope="row"><?php esc_html_e( 'Include Unsubscribe link?', 'mailchimp' ); ?></th>
					<td><input name="mc_use_unsub_link" type="checkbox"<?php checked( get_option( 'mc_use_unsub_link' ), 'on' ); ?> id="mc_use_unsub_link" class="code" />
						<em><label for="mc_use_unsub_link"><?php esc_html_e( 'We\'ll automatically  add a link to your list\'s unsubscribe form.', 'mailchimp' ); ?></label></em>
					</td>
				</tr>
				<tr valign="top">
					<td colspan="2">
					<?php
					echo wp_kses(
						sprintf(
							/* translators: %s: link to Mailchimp */
							__( '<strong>Note:</strong> If you haven\'t already, please <a href="%s" target="_blank" rel="noopener noreferrer">add</a> your website URL to your Mailchimp Audience account settings so users can properly return to your site after subscribing.', 'mailchimp' ),
							'https://mailchimp.com/help/change-or-update-the-return-to-our-website-button/'
						),
						[
							'a'      => [
								'href'   => [],
								'target' => [],
								'rel'    => [],
							],
							'strong' => [],
						]
					)
					?>
					</td>
				</tr>
			</table>
			<input type="submit" value="<?php esc_attr_e( 'Update Subscribe Form Settings', 'mailchimp' ); ?>" class="button mailchimp-sf-button small mc-submit" /><br/>
		</div>

		<?php
		$mv = get_option( 'mc_merge_vars' );

		if ( ! is_array( $mv ) || count( $mv ) === 0 ) {
			?>
			<div class="mc-section">
				<table class='widefat mc-widefat'>
					<tr><th><?php esc_html_e( 'Merge Fields Included', 'mailchimp' ); ?></th></tr>
					<tr><td><em><?php esc_html_e( 'No Merge Fields found.', 'mailchimp' ); ?></em></td></tr>
				</table>
			</div>
			<?php
		} else {
			?>
			<div class="mc-section">
				<table class='widefat mc-widefat'>
					<tr>
						<th colspan="4">
							<?php esc_html_e( 'Merge Fields Included', 'mailchimp' ); ?>
						</th>
					</tr>
					<tr valign="top">
						<th><?php esc_html_e( 'Name', 'mailchimp' ); ?></th>
						<th><?php esc_html_e( 'Tag', 'mailchimp' ); ?></th>
						<th><?php esc_html_e( 'Required?', 'mailchimp' ); ?></th>
						<th><?php esc_html_e( 'Include?', 'mailchimp' ); ?></th>
					</tr>
					<?php
					foreach ( $mv as $mv_var ) {
						?>
						<tr valign="top">
							<td><?php echo esc_html( $mv_var['name'] ); ?></td>
							<td><?php echo esc_html( $mv_var['tag'] ); ?></td>
							<td><?php echo esc_html( ( 1 === intval( $mv_var['required'] ) ) ? 'Y' : 'N' ); ?></td>
							<td>
								<?php
								if ( ! $mv_var['required'] ) {
									$opt = 'mc_mv_' . $mv_var['tag'];
									?>
									<label class="screen-reader-text" for="<?php echo esc_attr( $opt ); ?>">
										<?php
										echo esc_html(
											sprintf(
												/* translators: %s: name of field */
												__( 'Include merge field %s?', 'mailchimp' ),
												$mv_var['name']
											)
										);
										?>
									</label>
									<input name="<?php echo esc_attr( $opt ); ?>" type="checkbox" id="<?php echo esc_attr( $opt ); ?>" class="code"<?php checked( get_option( $opt ), 'on' ); ?> />
									<?php
								} else {
									?>
									&nbsp;&mdash;&nbsp;
									<?php
								}
								?>
							</td>
						</tr>
						<?php
					}
					?>
				</table>
				<input type="submit" value="<?php esc_attr_e( 'Update Subscribe Form Settings', 'mailchimp' ); ?>" class="button mailchimp-sf-button small mc-submit" /><br/>
			</div>

			<?php
			// Interest Groups Table
			$igs = get_option( 'mc_interest_groups' );
			if ( is_array( $igs ) && ! empty( $igs ) ) {
				?>
				<div class="mc-section">
					<h3 class="mc-h3"><?php esc_html_e( 'Group Settings', 'mailchimp' ); ?></h3>
				</div>
				<?php
				// Determines whether or not to continue processing. Only false if there was an error.
				$continue = true;
				foreach ( $igs as $ig ) {
					if ( $continue ) {
						if ( ! is_array( $ig ) || empty( $ig ) || 'N' === $ig ) {
							?>
							<em><?php esc_html_e( 'No Interest Groups Setup for this List', 'mailchimp' ); ?></em>
							<?php
							$continue = false;
						} else {
							?>
							<table class='mc-widefat' width="450px" cellspacing="0">
								<tr valign="top">
									<th colspan="2"><?php echo esc_html( $ig['title'] ); ?></th>
								</tr>
								<tr valign="top">
									<th>
										<label for="<?php echo esc_attr( 'mc_show_interest_groups_' . $ig['id'] ); ?>"><?php esc_html_e( 'Show?', 'mailchimp' ); ?></label>
									</th>
									<td>
										<input name="<?php echo esc_attr( 'mc_show_interest_groups_' . $ig['id'] ); ?>" id="<?php echo esc_attr( 'mc_show_interest_groups_' . $ig['id'] ); ?>" type="checkbox" class="code"<?php checked( 'on', get_option( 'mc_show_interest_groups_' . $ig['id'] ) ); ?> />
									</td>
								</tr>
								<tr valign="top">
									<th><?php esc_html_e( 'Input Type', 'mailchimp' ); ?></th>
									<td><?php echo esc_html( $ig['type'] ); ?></td>
								</tr>
								<tr valign="top" class="last-row">
									<th><?php esc_html_e( 'Options', 'mailchimp' ); ?></th>
									<td>
										<ul>
											<?php
											foreach ( $ig['groups'] as $interest ) {
												?>
												<li><?php echo esc_html( $interest['name'] ); ?></li>
												<?php
											}
											?>
										</ul>
									</td>
								</tr>
							</table>
							<?php
						}
					}
				}
			}
		}
		?>
	</form>
</div>
