<?php
/**
 * View file for the setup page.
 *
 * @package Mailchimp
 */

use function Mailchimp\WordPress\Includes\Admin\admin_notice_error;

$user = get_option( 'mc_user' );

// If we have an API Key, see if we need to change the lists and its options
mailchimp_sf_change_list_if_necessary();

$is_list_selected = false;
?>
<div class="wrap">
	<hr class="wp-header-end" />
	<table class="mc-user" cellspacing="0">
		<tr>
			<td><h3><?php esc_html_e( 'Logged in as', 'mailchimp' ); ?>: <?php echo esc_html( $user['username'] ); ?></h3>
			</td>
			<td>
				<form method="post" action="" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure you want to log out?', 'mailchimp' ) ); ?>');">
					<input type="hidden" name="mcsf_action" value="logout"/>
					<input type="submit" name="Submit" value="<?php esc_attr_e( 'Logout', 'mailchimp' ); ?>" class="button button-secondary mailchimp-sf-button small" />
					<?php wp_nonce_field( 'mc_logout', '_mcsf_nonce_action' ); ?>
				</form>
			</td>
		</tr>
	</table>
	<?php
	// Just get out if nothing else matters...
	$api = mailchimp_sf_get_api();
	if ( ! $api ) {
		return;
	}
	?>
	<h3 class="mc-h2"><?php esc_html_e( 'Your Lists', 'mailchimp' ); ?></h3>

	<div>
		<p class="mc-p"><?php esc_html_e( 'Please select the Mailchimp list you\'d like to connect to your form.', 'mailchimp' ); ?></p>
		<p class="mc-list-note"><strong><?php esc_html_e( 'Note:', 'mailchimp' ); ?></strong> <?php esc_html_e( 'Updating your list will not remove list settings in this plugin, but changing lists will.', 'mailchimp' ); ?></p>

		<form method="post" action="<?php echo esc_url( add_query_arg( array( 'page' => 'mailchimp_sf_options' ), admin_url( 'admin.php' ) ) ); ?>">
			<?php
			// we *could* support paging, but few users have that many lists (and shouldn't)
			$lists = $api->get( 'lists', 100, array( 'fields' => 'lists.id,lists.name,lists.email_type_option' ) );
			if ( is_wp_error( $lists ) ) {
				$msg = sprintf(
					/* translators: %s: error message */
					esc_html__( 'Uh-oh, we couldn\'t get your lists from Mailchimp! Error: %s', 'mailchimp' ),
					esc_html( $lists->get_error_message() )
				);
				admin_notice_error( $msg );
			} elseif ( isset( $lists['lists'] ) && count( $lists['lists'] ) === 0 ) {
				$msg = sprintf(
					/* translators: %s: link to Mailchimp */
					esc_html__( 'Uh-oh, you don\'t have any lists defined! Please visit %s, login, and setup a list before using this tool!', 'mailchimp' ),
					"<a href='http://www.mailchimp.com/'>Mailchimp</a>"
				);
				admin_notice_error( $msg );
			} else {
				$lists            = $lists['lists'];
				$option           = get_option( 'mc_list_id' );
				$list_ids         = array_map(
					function ( $ele ) {
						return $ele['id'];
					},
					$lists
				);
				$is_list_selected = in_array( $option, $list_ids, true );
				?>
				<table class="mc-list-select" cellspacing="0">
					<tr class="mc-list-row">
						<td>
							<label class="screen-reader-text" for="mc_list_id"><?php esc_html_e( 'Select a list', 'mailchimp' ); ?></label>
							<select id="mc_list_id" name="mc_list_id" style="min-width:200px;">
								<option value=""> &mdash; <?php esc_html_e( 'Select A List', 'mailchimp' ); ?> &mdash; </option>
								<?php
								foreach ( $lists as $list ) {
									?>
									<option value="<?php echo esc_attr( $list['id'] ); ?>"<?php selected( $list['id'], $option ); ?>><?php echo esc_html( $list['name'] ); ?></option>
									<?php
								}
								?>
							</select>
						</td>
						<td>
							<input type="hidden" name="mcsf_action" value="update_mc_list_id" />
							<input type="submit" name="Submit" value="<?php esc_attr_e( 'Update List', 'mailchimp' ); ?>" class="button mailchimp-sf-button small" />
						</td>
					</tr>
				</table>
				<?php
			} //end select list
			?>
		</form>
	</div>

	<br/>

	<?php
	// Just get out if nothing else matters...
	if ( ! $is_list_selected ) {
		return;
	}

	// The main Settings form
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
</div>
