<?php
/**
 * Template for the user sync page.
 *
 * @package Mailchimp
 */

global $wp_settings_sections, $wp_settings_fields;
?>
<div class="mailchimp-sf-user-sync-page-wrapper">
	<div class="mailchimp-sf-user-sync-page mailchimp-sf-section">
		<form action="options.php" method="post" class="mailchimp-sf-user-sync-form">
			<table class="widefat mailchimp-sf-settings-table">
				<thead>
					<tr>
						<th>
							<h2 class="mailchimp-sf-settings-table-title"><?php esc_html_e( 'User sync settings', 'mailchimp' ); ?></h2>
							<p class="mailchimp-sf-settings-table-description"><?php esc_html_e( 'Sync your contacts between WordPress and Mailchimp', 'mailchimp' ); ?></p>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr valign="top">
						<td>
							<div class="mailchimp-sf-user-sync-settings-fields">
								<?php
								$page = 'mailchimp_sf_user_sync_settings';
								settings_fields( $page );

								if ( isset( $wp_settings_sections[ $page ] ) ) {
									foreach ( $wp_settings_sections[ $page ] as $section ) {
										if ( isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
											foreach ( (array) $wp_settings_fields[ $page ][ $section['id'] ] as $field ) {
												?>
												<div class="mailchimp-sf-user-sync-settings-field <?php echo esc_attr( $field['args']['class'] ?? '' ); ?>">
													<?php
													echo '<div class="mailchimp-sf-user-sync-settings-field-label">';
													if ( ! empty( $field['args']['label_for'] ) ) {
														echo '<label for="' . esc_attr( $field['args']['label_for'] ) . '">' . esc_html( $field['title'] ) . '</label>';
													} else {
														echo '<span>' . esc_html( $field['title'] ) . '</span>';
													}
													echo '</div>';

													echo '<div class="mailchimp-sf-user-sync-settings-field-content">';
													call_user_func( $field['callback'], $field['args'] );
													echo '</div>';
													?>
												</div>
												<?php
											}
										}
									}
								}
								?>
							</div>
						</td>
					</tr>
				</tbody>
			</table>

			<div class="mailchimp-sf-section-footer">
				<?php
				submit_button( __( 'Save Changes', 'mailchimp' ), 'mailchimp-sf-button mailchimp-sf-button-submit btn-primary', 'mailchimp_sf_user_sync_settings_submit' );
				?>
			</div>
		</form>

		<?php
		/**
		 * Render the user sync errors.
		 */
		do_action( 'mailchimp_sf_user_sync_after_form' );
		?>
	</div>
	<div class="mailchimp-sf-user-sync-settings-column"></div>
</div>
