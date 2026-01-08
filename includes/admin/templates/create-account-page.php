<?php
/**
 * Create account page template
 *
 * @package Mailchimp
 */

$admin_email = get_option( 'admin_email' );
$user        = get_user_by( 'email', $admin_email );

if ( empty( $user ) ) {
	$user_id = get_current_user_id();
	$user    = get_user_by( 'id', $user_id );
}

$profile                = array();
$email                  = $user->user_email ?? '';
$waiting_login          = get_option( 'mailchimp_sf_waiting_for_login' );
$api                    = mailchimp_sf_get_api();
$screen                 = get_current_screen();
$is_create_account_page = $screen && 'admin_page_mailchimp_sf_create_account' === $screen->id;
$is_retrying            = isset( $_GET['retry'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['retry'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$signup_initiated       = $waiting_login && 'waiting' === $waiting_login && ! $is_retrying;

if ( ! empty( $api ) ) {
	$profile = $api->get( '' );
	if ( ! is_wp_error( $profile ) ) {
		$email = $profile['email'] ?? $email;
	}
}

// Prepare data for prefilling the form.
$data = array(
	'first_name'    => $user->first_name ?? '',
	'last_name'     => $user->last_name ?? '',
	'business_name' => get_bloginfo( 'name' ),
	'phone_number'  => '',
	'email'         => $email,
	'address'       => '',
	'address2'      => '',
	'city'          => '',
	'state'         => '',
	'zip'           => '',
	'country'       => '',
	'timezone'      => wp_timezone_string(),
);

// Update prefill data if the user has already filled the form earlier and retrying.
if ( $is_retrying && ! empty( $profile ) ) {
	$contact = $profile['contact'] ?? array();
	$data    = array(
		'first_name'    => $profile['first_name'] ?? $data['first_name'],
		'last_name'     => $profile['last_name'] ?? $data['last_name'],
		'business_name' => $profile['account_name'] ?? $data['business_name'],
		'phone_number'  => $data['phone_number'],
		'email'         => $profile['email'] ?? $data['email'],
		'address'       => $contact['addr1'] ?? $data['address'],
		'address2'      => $contact['addr2'] ?? $data['address2'],
		'city'          => $contact['city'] ?? $data['city'],
		'state'         => $contact['state'] ?? $data['state'],
		'zip'           => $contact['zip'] ?? $data['zip'],
		'country'       => $contact['country'] ?? $data['country'],
		'timezone'      => $profile['account_timezone'] ?? $data['timezone'],
	);
}

?>
<div class="mailchimp-sf-create-account">
	<?php
	// Header.
	include_once MCSF_DIR . 'includes/admin/templates/header.php'; // phpcs:ignore PEAR.Files.IncludingFile.UseRequireOnce
	?>
	<div class="mailchimp-sf-create-account__body wrap">
		<div class="mailchimp-sf-admin-notices">
			<hr class="wp-header-end" style="display:none;"/>
		</div>
		<div class="mailchimp-sf-create-account__body-inner <?php echo esc_attr( ( $signup_initiated ) ? 'hidden' : '' ); ?>">
			<form class="mailchimp-sf-activate-account">
				<div class="title"><?php esc_html_e( 'Confirm your information', 'mailchimp' ); ?></div>
				<div id="mailchimp-sf-profile-details" class="mailchimp-sf-create-account-step">
					<div class="general-error">
						<p class="error-message"></p>
					</div>
					<div class="subtitle"><?php esc_html_e( 'Profile details', 'mailchimp' ); ?></div>
					<div class="mailchimp-sf-form-wrapper">
						<fieldset>
							<input id="org" name="org" type="hidden" value="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
							<div class="form-row">
								<div class="box box-half">
									<label for="first_name">
										<span><?php esc_html_e( 'First name', 'mailchimp' ); ?></span>
									</label>
									<input required type="text" id="first_name" name="first_name" value="<?php echo esc_attr( $data['first_name'] ); ?>"/>
									<p id="mailchimp-sf-first_name-error" class="error-field"></p>
								</div>
								<div class="box box-half">
									<label for="last_name">
										<span><?php esc_html_e( 'Last name', 'mailchimp' ); ?></span>
									</label>
									<input required type="text" id="last_name" name="last_name" value="<?php echo esc_attr( $data['last_name'] ); ?>"/>
									<p id="mailchimp-sf-last_name-error" class="error-field"></p>
								</div>
							</div>

							<div class="form-row">
								<div class="box box-half">
									<label for="business_name">
										<span><?php esc_html_e( 'Business name', 'mailchimp' ); ?></span>
									</label>
									<input required type="text" id="business_name" name="business_name" value="<?php echo esc_attr( $data['business_name'] ); ?>"/>
									<p id="mailchimp-sf-business_name-error" class="error-field"></p>
									<p class="help-text"><?php esc_html_e( 'You can always change this later in your account settings.', 'mailchimp' ); ?></p>
								</div>
								<div class="box box-half">
									<label for="phone_number" class="optional flex justify-between">
										<span><?php esc_html_e( 'Phone number', 'mailchimp' ); ?></span>
										<span class="optional"><?php esc_html_e( 'Optional', 'mailchimp' ); ?></span>
									</label>
									<input type="text" id="phone_number" name="phone_number" value="<?php echo esc_attr( $data['phone_number'] ); ?>"/>
								</div>
							</div>

							<div class="form-row">
								<div class="box">
									<label for="email">
										<span><?php esc_html_e( 'Email', 'mailchimp' ); ?></span>
									</label>
									<input required type="email" id="email" name="email" value="<?php echo esc_attr( $data['email'] ); ?>"/>
									<p id="mailchimp-sf-email-error" class="error-field"></p>

								</div>
							</div>
							<div class="form-row">
								<div class="box">
									<label for="confirm_email">
										<span><?php esc_html_e( 'Confirm Email', 'mailchimp' ); ?></span>
									</label>
									<input required type="email" id="confirm_email" name="confirm_email"/>
									<p id="mailchimp-sf-confirm_email-error" class="error-field"></p>
								</div>
							</div>
						</fieldset>
					</div>
				</div>

				<div id="mailchimp-sf-business-address" class="mailchimp-sf-create-account-step">
					<div class="subtitle"><?php esc_html_e( 'Business Address', 'mailchimp' ); ?></div>

					<div class="mailchimp-sf-form-wrapper">
						<fieldset>
							<div class="form-row">
								<div class="box">
									<label for="address">
										<span><?php esc_html_e( 'Address line 1 (Street address or post office box)', 'mailchimp' ); ?></span>
									</label>
									<input required type="text" id="address" name="address" value="<?php echo esc_attr( $data['address'] ); ?>"/>
									<p id="mailchimp-sf-address-error" class="error-field"></p>
								</div>
							</div>

							<div class="form-row">
								<div class="box">
									<label for="address2" class="optional flex justify-between">
										<span><?php esc_html_e( 'Address line 2', 'mailchimp' ); ?></span>
										<span class="optional"><?php esc_html_e( 'Optional', 'mailchimp' ); ?></span>
									</label>
									<input type="text" id="address2" name="address2" value="<?php echo esc_attr( $data['address2'] ); ?>"/>
								</div>
							</div>

							<div class="form-row">
								<div class="box box-half">
									<label for="city">
										<span><?php esc_html_e( 'City', 'mailchimp' ); ?></span>
									</label>
									<input required type="text" id="city" name="city" value="<?php echo esc_attr( $data['city'] ); ?>"/>
									<p id="mailchimp-sf-city-error" class="error-field"></p>
								</div>
								<div class="box box-half">
									<label for="state">
										<span><?php esc_html_e( 'State/Province/Region', 'mailchimp' ); ?></span>
									</label>
									<input required type="text" id="state" name="state" value="<?php echo esc_attr( $data['state'] ); ?>"/>
									<p id="mailchimp-sf-state-error" class="error-field"></p>
								</div>
							</div>

							<div class="form-row">
								<div class="box box-half">
									<label for="zip">
										<span><?php esc_html_e( 'Zip/Postal code', 'mailchimp' ); ?></span>
									</label>
									<input required type="text" id="zip" name="zip" value="<?php echo esc_attr( $data['zip'] ); ?>"/>
									<p id="mailchimp-sf-zip-error" class="error-field"></p>
								</div>
								<div class="box box-half">
									<label for="country">
										<span><?php esc_html_e( 'Country', 'mailchimp' ); ?></span>
									</label>
									<div class="mailchimp-select-wrapper">
										<select id="country" name="country" required>
											<option value=""><?php esc_html_e( 'Please select a country', 'mailchimp' ); ?></option>
										<?php
										$selected_country = $data['country'];
										foreach ( $countries as $key => $value ) {
											echo '<option value="' . esc_attr( $key ) . '" ' . selected( $selected_country, $key, false ) . '>' . esc_html( $value ) . '</option>';
										}
										?>
										</select>
										<p id="mailchimp-sf-country-error" class="error-field"></p>
									</div>
								</div>
							</div>
						</fieldset>
						<?php
						$timezone          = 'UTC';
						$selected_timezone = $data['timezone'];
						if ( in_array( $selected_timezone, $timezones, true ) ) {
							$timezone = $selected_timezone;
						}
						?>
						<input type="hidden" id="timezone" name="timezone" value="<?php echo esc_attr( $timezone ); ?>"/>
					</div>
				</div>

				<div class="box terms">
					<p>
						<?php
						echo wp_kses(
							sprintf(
								/* translators: %s - Mailchimp legal pages */
								__( 'To follow <a href="%1$s" target="_blank">anti-spam laws</a>, your address will appear in the footer of every email you send with Mailchimp. Don\'t have an official business address? Learn about <a href="%2$s" target="_blank">alternatives</a>.', 'mailchimp' ),
								esc_url( 'https://mailchimp.com/help/anti-spam-requirements-for-email/' ),
								esc_url( 'https://mailchimp.com/help/alternative-physical-address-ideas/' )
							),
							array(
								'a' => array(
									'href'   => array(),
									'target' => '_blank',
								),
							)
						);
						?>
					</p>
					<p>
						<?php
						echo wp_kses(
							sprintf(
								/* translators: %s - Mailchimp legal pages */
								__( 'By creating an account, you agree to our <a href="%1$s" target="_blank">Terms</a> and have read and acknowledge the <a href="%2$s" target="_blank">Global Privacy Statement</a>.', 'mailchimp' ),
								esc_url( 'https://mailchimp.com/legal/terms/' ),
								esc_url( 'https://www.intuit.com/privacy/statement/' )
							),
							array(
								'a' => array(
									'href'   => array(),
									'target' => '_blank',
								),
							)
						);
						?>
					</p>
				</div>
				<div class="box">
					<button type="submit" id="mailchimp-sf-create-activate-account" class="button button-primary mailchimp-sf-button">
							<span class="mailchimp-sf-loading hidden">
								<svg class="animate-spin" width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
										<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
										<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
								</svg>
							</span>
							<?php esc_html_e( 'Activate account', 'mailchimp' ); ?>
					</button>
				</div>
			</form>
			<input type="hidden" name="signup_initiated" value="<?php echo esc_attr( (bool) $signup_initiated ); ?>" />
		</div>
		<?php
		// Activate account message.
		include_once MCSF_DIR . 'includes/admin/templates/activate-account.php'; // phpcs:ignore PEAR.Files.IncludingFile.UseRequireOnce

		// Suggest to login message.
		include_once MCSF_DIR . 'includes/admin/templates/suggest-to-login.php'; // phpcs:ignore PEAR.Files.IncludingFile.UseRequireOnce
		?>
	</div>
</div>
