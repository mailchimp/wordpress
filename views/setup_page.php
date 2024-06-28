<?php
/**
 * View file for the setup page.
 *
 * @package Mailchimp
 */

?>
<div class="wrap">

	<div class="mailchimp-header">
		<svg xmlns="http://www.w3.org/2000/svg" aria-label="<?php esc_attr_e( 'Mailchimp Freddie', 'mailchimp_i18n' ); ?>" width="40" height="40" fill="none" viewBox="0 0 40 40">
			<title><?php esc_html_e( 'Mailchimp Freddie', 'mailchimp_i18n' ); ?></title>
			<path fill="#241C15" fill-rule="evenodd" d="M35.363 24.328c.507 0 1.303.587 1.303 2.003 0 1.408-.581 3.004-.718 3.358-2.095 5.046-7.095 7.855-13.052 7.677-5.552-.166-10.288-3.11-12.36-7.911-1.254 0-2.546-.553-3.528-1.427-1.035-.922-1.673-2.114-1.798-3.358-.096-.969.022-1.87.324-2.655l-1.164-.992c-5.326-4.52 11.333-23.13 16.661-18.459.027.024 1.813 1.785 1.817 1.789l.99-.422c4.674-1.945 8.466-1.006 8.47 2.097.003 1.614-1.02 3.495-2.66 5.202.595.554 1.07 1.42 1.343 2.41.23.731.27 1.473.29 1.949.03.658.06 2.19.064 2.217.042.013.515.143.658.184 1.255.354 2.147.825 2.584 1.286.436.46.652.905.731 1.428.074.422.064 1.166-.49 1.999 0 0 .143.313.281.757.138.445.238.814.254.868Zm-12.941 2.335.002.005-.002-.004v-.001Zm13.002.145c.127-.843-.06-1.17-.313-1.326-.268-.166-.59-.108-.59-.108s-.146-1.009-.56-1.924c-1.23.975-2.814 1.66-4.02 2.009-1.391.401-3.274.71-5.375.584-1.165-.095-1.936-.437-2.226.51 2.662.98 5.48.56 5.48.56a.1.1 0 0 1 .109.09.107.107 0 0 1-.062.106s-2.164 1.01-5.602-.059c.095.81.881 1.174 1.257 1.32.472.186.99.271.99.271 4.26.736 8.242-1.712 9.14-2.328.066-.046.11-.001.057.08a1.467 1.467 0 0 1-.088.126c-1.097 1.423-4.048 3.07-7.886 3.07-1.674 0-3.348-.593-3.962-1.504-.953-1.413-.047-3.476 1.541-3.261l.696.079c1.985.222 4.859-.058 7.228-1.163 2.167-1.01 2.985-2.123 2.862-3.024a1.298 1.298 0 0 0-.373-.736c-.39-.383-1.007-.681-2.049-.975-.344-.097-.578-.16-.83-.243-.447-.148-.668-.267-.718-1.112-.021-.37-.086-1.658-.109-2.19-.04-.933-.152-2.207-.94-2.734a1.315 1.315 0 0 0-.672-.216c-.235-.01-.352.031-.4.04-.449.076-.714.316-1.045.594-.982.822-1.81.957-2.732.917-.551-.024-1.135-.11-1.804-.15-.13-.007-.26-.016-.39-.022-1.545-.08-3.2 1.26-3.475 3.163-.383 2.649 1.525 4.017 2.076 4.82.07.096.152.232.152.36 0 .154-.1.276-.197.38-1.574 1.628-2.078 4.214-1.485 6.369.074.27.168.526.28.772 1.392 3.27 5.712 4.794 9.932 3.409.565-.186 1.1-.416 1.603-.681a8.429 8.429 0 0 0 2.456-1.844c1.12-1.176 1.785-2.454 2.044-4.03Zm-7.017-8.188c-.197-.252-.373-.659-.472-1.135-.176-.846-.157-1.46.335-1.539.492-.08.73.432.905 1.279.119.569.096 1.092-.035 1.395a2.838 2.838 0 0 0-.733 0Zm-4.224.67c-.352-.156-.809-.329-1.36-.295-.782.048-1.46.393-1.655.37-.083-.011-.118-.047-.128-.094-.032-.146.191-.386.427-.56.713-.515 1.638-.626 2.413-.29.38.162.736.45.91.736.084.138.1.245.046.3-.085.09-.3-.012-.653-.167Zm-.708.406c.631-.076 1.094.22 1.202.395.046.075.028.125.013.148-.05.08-.16.065-.39.039-.417-.048-.838-.076-1.476.154 0 0-.232.093-.336.093a.11.11 0 0 1-.11-.113c0-.094.085-.229.223-.35.163-.142.416-.294.874-.366Zm3.505 1.49c-.31-.153-.473-.463-.361-.691.111-.228.454-.289.765-.135.312.154.474.463.362.692-.111.228-.454.288-.765.134Zm2.002-1.757c.253.004.453.29.447.639-.006.348-.216.627-.468.623-.253-.004-.454-.29-.448-.639.006-.349.216-.628.469-.623Zm-13.137-7.626c-.047.054.022.131.08.089 1.14-.834 2.704-1.61 4.752-2.111 2.295-.563 4.504-.327 5.853-.016.068.015.11-.102.05-.136-.891-.503-2.26-.844-3.23-.851-.049 0-.075-.056-.047-.095.168-.226.398-.45.608-.613.047-.036.019-.113-.04-.11-1.383.086-2.959.751-3.865 1.372-.044.03-.102-.01-.091-.063.07-.342.293-.792.409-1.003.027-.05-.027-.104-.077-.078-1.456.748-3.082 2.082-4.402 3.615Zm-6.86 7.294c1.521-4.105 4.063-7.889 7.426-10.492C18.908 6.512 21.6 5.01 21.6 5.01s-1.45-1.69-1.888-1.814c-2.693-.732-8.51 3.3-12.225 8.628-1.503 2.156-3.655 5.973-2.626 7.936.127.244.844.869 1.23 1.192.644-.941 1.697-1.622 2.893-1.856Zm2.008 9.01c1.947-.334 2.456-2.459 2.136-4.545-.363-2.356-1.948-3.187-3.025-3.246-.299-.016-.576.011-.806.058-1.92.389-3.005 2.031-2.792 4.165.193 1.93 2.137 3.558 3.935 3.606.187.005.371-.008.552-.038Zm.736-2.425c.1-.024.204-.048.267.031.022.026.058.086.016.185-.072.167-.355.396-.76.38-.416-.032-.88-.336-.942-1.094-.031-.374.11-.83.196-1.067.167-.461.016-.944-.376-1.201a1.011 1.011 0 0 0-1.408.294c-.12.188-.193.423-.232.55l-.025.077c-.088.238-.23.308-.324.295-.045-.006-.107-.037-.147-.146-.108-.298-.02-1.142.539-1.762.354-.394.91-.595 1.45-.526.563.072 1.03.414 1.317.962.381.73.042 1.495-.16 1.953l-.06.136c-.127.303-.134.568-.019.746a.511.511 0 0 0 .432.22c.087.002.166-.017.236-.033Z" clip-rule="evenodd"/>
		</svg>
		<h1><?php esc_html_e( 'Mailchimp List Subscribe Form', 'mailchimp_i18n' ); ?> </h1>
	</div>
<?php

$user = get_option( 'mc_user' );
/* TODO MC SOPRESTO USER INFO */

// If we have an API Key, see if we need to change the lists and its options
mailchimp_sf_change_list_if_necessary();

// Display our success/error message(s) if have them
if ( mailchimp_sf_global_msg() !== '' ) {
	?>
	<div id="mc-message" class=""><?php echo wp_kses_post( mailchimp_sf_global_msg() ); ?></div>
	<?php
}

// If we don't have an API Key, do a login form
if ( ! $user || ! get_option( 'mc_api_key' ) ) {
	?>
	<div>
		<h3 class="mc-h2"><?php esc_html_e( 'Log In', 'mailchimp_i18n' ); ?></h3>
		<p class="mc-p" style="width: 40%;">
		<?php
			echo wp_kses(
				__(
					'To get started, we\'ll need to access your Mailchimp account with an <a href="http://kb.mailchimp.com/integrations/api-integrations/about-api-keys">API Key</a>. Paste your Mailchimp API key, and click <strong>Connect</strong> to continue.',
					'mailchimp_i18n'
				),
				[
					'a'      => [
						'href' => [],
					],
					'strong' => [],
				]
			);
		?>
		</p>
		<p class="mc-p">
			<?php
			printf(
				'%1$s <a href="http://www.mailchimp.com/signup/" target="_blank">%2$s</a>',
				esc_html( __( 'Don\'t have a Mailchimp account?', 'mailchimp_i18n' ) ),
				esc_html( __( 'Try one for Free!', 'mailchimp_i18n' ) )
			);
			?>
		</p>
		<div class="mc-section">
			<table class="widefat mc-widefat mc-api">
			<form method="POST" action="">
				<tr valign="top">
					<th scope="row" class="mailchimp-connect"><?php esc_html_e( 'Connect to Mailchimp', 'mailchimp_i18n' ); ?></th>
					<td>
						<input type="hidden" name="mcsf_action" value="login"/>
						<input type="password" name="mailchimp_sf_api_key" placeholder="API Key">
					</td>
					<td>
						<input class="button mc-submit" type="submit" value="Connect">
					</td>
				</tr>
			</form>
			</table>
		</div>
	</div>

	<br/>
	<?php
	if ( '' !== $user && isset( $user['username'] ) && $user['username'] ) {
		?>
<!--<div class="notes_msg">
		<strong><?php esc_html_e( 'Notes', 'mailchimp_i18n' ); ?>:</strong>
		<ul>
			<li><?php esc_html_e( 'Changing your settings at Mailchimp.com may cause this to stop working.', 'mailchimp_i18n' ); ?></li>
			<li><?php esc_html_e( 'If you change your login to a different account, the info you have setup below will be erased.', 'mailchimp_i18n' ); ?></li>
			<li><?php esc_html_e( 'If any of that happens, no biggie - just reconfigure your login and the items below...', 'mailchimp_i18n' ); ?></li>
		</ul>
</div>-->
		<?php
	}
	// End of login form
} else { // Start logout form
	?>
<table class="mc-user" cellspacing="0">
	<tr>
		<td><h3><?php esc_html_e( 'Logged in as', 'mailchimp_i18n' ); ?>: <?php echo esc_html( $user['username'] ); ?></h3>
		</td>
		<td>
			<form method="post" action="">
				<input type="hidden" name="mcsf_action" value="logout"/>
				<input type="submit" name="Submit" value="<?php esc_attr_e( 'Logout', 'mailchimp_i18n' ); ?>" class="button" />
				<?php wp_nonce_field( 'mc_logout', '_mcsf_nonce_action' ); ?>
			</form>
		</td>
	</tr>
</table>
	<?php
} // End Logout form

// Just get out if nothing else matters...
$api = mailchimp_sf_get_api();
if ( ! $api ) { return; }

if ( $api ) {
	?>
	<h3 class="mc-h2"><?php esc_html_e( 'Your Lists', 'mailchimp_i18n' ); ?></h3>

<div>

	<p class="mc-p"><?php esc_html_e( 'Please select the Mailchimp list you\'d like to connect to your form.', 'mailchimp_i18n' ); ?></p>
	<p class="mc-list-note"><strong><?php esc_html_e( 'Note:', 'mailchimp_i18n' ); ?></strong> <?php esc_html_e( 'Updating your list will not remove list settings in this plugin, but changing lists will.', 'mailchimp_i18n' ); ?></p>

	<form method="post" action="<?php echo esc_url( add_query_arg( array( 'page' => 'mailchimp_sf_options' ), admin_url( 'admin.php' ) ) ); ?>">
		<?php
		// we *could* support paging, but few users have that many lists (and shouldn't)
		$lists = $api->get( 'lists', 100, array( 'fields' => 'lists.id,lists.name,lists.email_type_option' ) );
		$lists = $lists['lists'];

		if ( count( $lists ) === 0 ) {
			?>
			<div class="error_msg">
				<?php
				printf(
					// translators: placeholder is a link to Mailchimp
					esc_html( __( 'Uh-oh, you don\'t have any lists defined! Please visit %s, login, and setup a list before using this tool!', 'mailchimp_i18n' ) ),
					"<a href='http://www.mailchimp.com/'>Mailchimp</a>"
				);
				?>
			</div>
			<?php
		} else {
			?>
		<table class="mc-list-select" cellspacing="0">
			<tr class="mc-list-row">
				<td>
					<label class="screen-reader-text" for="mc_list_id"><?php esc_html_e( 'Select a list', 'mailchimp_i18n' ); ?></label>
					<select id="mc_list_id" name="mc_list_id" style="min-width:200px;">
						<option value=""> &mdash; <?php esc_html_e( 'Select A List', 'mailchimp_i18n' ); ?> &mdash; </option>
						<?php
						foreach ( $lists as $list ) {
							$option = get_option( 'mc_list_id' );
							?>
							<option value="<?php echo esc_attr( $list['id'] ); ?>"<?php selected( $list['id'], $option ); ?>><?php echo esc_html( $list['name'] ); ?></option>
							<?php
						}
						?>
					</select>
				</td>
				<td>
					<input type="hidden" name="mcsf_action" value="update_mc_list_id" />
					<input type="submit" name="Submit" value="<?php esc_attr_e( 'Update List', 'mailchimp_i18n' ); ?>" class="button" />
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
} else { // display the selected list...
	?>

<p class="submit">
	<form method="post" action="<?php echo esc_url( add_query_arg( array( 'page' => 'mailchimp_sf_options' ), admin_url( 'admin.php' ) ) ); ?>">
		<input type="hidden" name="mcsf_action" value="reset_list" />
		<input type="submit" name="reset_list" value="<?php esc_attr_e( 'Reset List Options and Select again', 'mailchimp_i18n' ); ?>" class="button" />
		<?php wp_nonce_field( 'reset_mailchimp_list', '_mcsf_nonce_action' ); ?>
	</form>
</p>
<h3><?php esc_html_e( 'Subscribe Form Widget Settings for this List', 'mailchimp_i18n' ); ?>:</h3>
<h4><?php esc_html_e( 'Selected Mailchimp List', 'mailchimp_i18n' ); ?>: <?php echo esc_html( get_option( 'mc_list_name' ) ); ?></h4>
	<?php
}

// Just get out if nothing else matters...
if ( get_option( 'mc_list_id' ) === '' ) {
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
	<tr><th colspan="2">Content Options</th></tr>
	<tr valign="top">
		<th scope="row">
			<label for="mc_header_content"><?php esc_html_e( 'Header', 'mailchimp_i18n' ); ?></label>
		</th>
		<td>
			<textarea class="widefat" id="mc_header_content" name="mc_header_content" rows="2"><?php echo wp_kses_post( get_option( 'mc_header_content' ) ); ?></textarea><br/>
			<?php esc_html_e( 'Add your own text, HTML markup (including image links), or keep it blank.', 'mailchimp_i18n' ); ?>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="mc_subheader_content"><?php esc_html_e( 'Sub-header', 'mailchimp_i18n' ); ?></label>
		</th>
		<td>
			<textarea class="widefat" id="mc_subheader_content" name="mc_subheader_content" rows="2"><?php echo wp_kses_post( get_option( 'mc_subheader_content' ) ); ?></textarea><br/>
			<?php esc_html_e( 'Add your own text, HTML markup (including image links), or keep it blank.', 'mailchimp_i18n' ); ?>.<br/>
			<?php esc_html_e( 'This will be displayed under the heading and above the form.', 'mailchimp_i18n' ); ?>
		</td>
	</tr>

	<tr valign="top" class="last-row">
		<th scope="row">
			<label for="mc_submit_text"><?php esc_html_e( 'Submit Button', 'mailchimp_i18n' ); ?></label>
		</th>
		<td>
			<input class="widefat" type="text" id="mc_submit_text" name="mc_submit_text" size="70" value="<?php echo esc_attr( get_option( 'mc_submit_text' ) ); ?>"/>
		</td>
	</tr>
</table>

<input type="submit" value="<?php esc_attr_e( 'Update Subscribe Form Settings', 'mailchimp_i18n' ); ?>" class="button mc-submit" /><br/>

<table class="widefat mc-widefat mc-nuke-styling">
<tr><th colspan="2">Remove Mailchimp CSS</th></tr>
<tr><th><label for="mc_nuke_all_styles"><?php esc_html_e( 'Remove CSS' ); ?></label></th><td><span class="mc-pre-input"></span><input type="checkbox" name="mc_nuke_all_styles" id="mc_nuke_all_styles" <?php checked( get_option( 'mc_nuke_all_styles' ), true ); ?> onclick="showMe('mc-custom-styling')"/><?php esc_html_e( 'This will disable all Mailchimp CSS, so it\'s recommended for WordPress experts only.' ); ?></td></tr>
</table>
<?php if ( get_option( 'mc_nuke_all_styles' ) === '1' ) { ?>
	<table class="widefat mc-widefat mc-custom-styling" id="mc-custom-styling" style="display:none">
	<?php } else { ?>
		<table class="widefat mc-widefat mc-custom-styling" id="mc-custom-styling">
	<?php } ?>
	<tr>
		<th colspan="2">Custom Styling</th>
	</tr>
	<tr>
		<th>
			<label for="mc_custom_style"><?php esc_html_e( 'Enabled?', 'mailchimp_i18n' ); ?></label>
		</th>
		<td>
			<span class="mc-pre-input"></span>
			<input type="checkbox" name="mc_custom_style" id="mc_custom_style"<?php checked( get_option( 'mc_custom_style' ), 'on' ); ?> />
			<em><?php esc_html_e( 'Edit the default Mailchimp CSS style.' ); ?></em>
		</td>
	</tr>
	<tr>
		<th>
			<label for="mc_form_border_width"><?php esc_html_e( 'Border Width (px)', 'mailchimp_i18n' ); ?></label>
		</th>
		<td>
			<input type="text" id="mc_form_border_width" name="mc_form_border_width" size="3" maxlength="3" value="<?php echo esc_attr( get_option( 'mc_form_border_width' ) ); ?>"/>
			<em><?php esc_html_e( 'Set to 0 for no border, do not enter', 'mailchimp_i18n' ); ?> px</em>
		</td>
	</tr>
	<tr>
		<th>
			<label for="mc_form_border_color"><?php esc_html_e( 'Border Color', 'mailchimp_i18n' ); ?></label>
		</th>
		<td>
			<span class="mc-pre-input">#</span>
			<input type="text" id="mc_form_border_color" name="mc_form_border_color" size="7" maxlength="6" value="<?php echo esc_attr( get_option( 'mc_form_border_color' ) ); ?>"/>
			<em><?php esc_html_e( 'Do not enter initial', 'mailchimp_i18n' ); ?> <strong>#</strong></em>
		</td>
	</tr>
	<tr>
		<th>
			<label for="mc_form_text_color"><?php esc_html_e( 'Text Color', 'mailchimp_i18n' ); ?></label>
		</th>
		<td>
			<span class="mc-pre-input">#</span>
			<input type="text" id="mc_form_text_color" name="mc_form_text_color" size="7" maxlength="6" value="<?php echo esc_attr( get_option( 'mc_form_text_color' ) ); ?>"/>
			<em><?php esc_html_e( 'Do not enter initial', 'mailchimp_i18n' ); ?> <strong>#</strong></em>
		</td>
	</tr>
	<tr class="last-row">
		<th>
			<label for="mc_form_background"><?php esc_html_e( 'Background Color', 'mailchimp_i18n' ); ?></label>
		</th>
		<td>
			<span class="mc-pre-input">#</span>
			<input type="text" id="mc_form_background" name="mc_form_background" size="7" maxlength="6" value="<?php echo esc_attr( get_option( 'mc_form_background' ) ); ?>"/>
			<em><?php esc_html_e( 'Do not enter initial', 'mailchimp_i18n' ); ?> <strong>#</strong></em>
		</td>
	</tr>
</table>

<input type="submit" value="<?php esc_attr_e( 'Update Subscribe Form Settings', 'mailchimp_i18n' ); ?>" class="button mc-submit" /><br/>


<table class="widefat mc-widefat">
	<tr><th colspan="2">List Options</th></tr>

	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Use JavaScript Support?', 'mailchimp_i18n' ); ?></th>
		<td><input name="mc_use_javascript" type="checkbox" <?php checked( get_option( 'mc_use_javascript' ), 'on' ); ?> id="mc_use_javascript" class="code" />
			<em><label for="mc_use_javascript"><?php esc_html_e( 'This plugin uses JavaScript submission, and it should degrade gracefully for users not using JavaScript. It is optional, and you can turn it off at any time.', 'mailchimp_i18n' ); ?></label></em>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Use JavaScript Datepicker?', 'mailchimp_i18n' ); ?></th>
		<td><input name="mc_use_datepicker" type="checkbox" <?php checked( get_option( 'mc_use_datepicker' ), 'on' ); ?> id="mc_use_datepicker" class="code" />
			<em><label for="mc_use_datepicker"><?php esc_html_e( 'We\'ll use the jQuery UI Datepicker for dates.', 'mailchimp_i18n' ); ?></label></em>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Use Double Opt-In (Recommended)?', 'mailchimp_i18n' ); ?></th>
		<td><input name="mc_double_optin" type="checkbox" <?php checked( get_option( 'mc_double_optin' ), true ); ?> id="mc_double_optin" class="code" />
			<em><label for="mc_double_optin"><?php esc_html_e( 'Before new your subscribers are added via the plugin, they\'ll need to confirm their email address.', 'mailchimp_i18n' ); ?></label></em>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Update existing subscribers?', 'mailchimp_i18n' ); ?></th>
		<td><input name="mc_update_existing" type="checkbox" <?php checked( get_option( 'mc_update_existing' ), true ); ?> id="mc_update_existing" class="code" />
			<em><label for="mc_update_existing"><?php esc_html_e( 'If an existing subscriber fills out this form, we will update their information with what\'s provided.', 'mailchimp_i18n' ); ?></label></em>
		</td>
	</tr>

	<tr valign="top" class="last-row">
		<th scope="row"><?php esc_html_e( 'Include Unsubscribe link?', 'mailchimp_i18n' ); ?></th>
		<td><input name="mc_use_unsub_link" type="checkbox"<?php checked( get_option( 'mc_use_unsub_link' ), 'on' ); ?> id="mc_use_unsub_link" class="code" />
			<em><label for="mc_use_unsub_link"><?php esc_html_e( 'We\'ll automatically  add a link to your list\'s unsubscribe form.', 'mailchimp_i18n' ); ?></label></em>
		</td>
	</tr>
</table>

</div>

<div class="mc-section">

	<input type="submit" value="<?php esc_attr_e( 'Update Subscribe Form Settings', 'mailchimp_i18n' ); ?>" class="button mc-submit" /><br/>

	<table class='widefat mc-widefat'>
		<tr>
			<th colspan="4">
				<?php esc_html_e( 'Merge Fields Included', 'mailchimp_i18n' ); ?>

				<?php
				$mv = get_option( 'mc_merge_vars' );

				if ( ! is_array( $mv ) || count( $mv ) === 0 ) {
					?>
					<em><?php esc_html_e( 'No Merge Fields found.', 'mailchimp_i18n' ); ?></em>
					<?php
				} else {
					?>
			</th>
		</tr>
		<tr valign="top">
			<th><?php esc_html_e( 'Name', 'mailchimp_i18n' ); ?></th>
			<th><?php esc_html_e( 'Tag', 'mailchimp_i18n' ); ?></th>
			<th><?php esc_html_e( 'Required?', 'mailchimp_i18n' ); ?></th>
			<th><?php esc_html_e( 'Include?', 'mailchimp_i18n' ); ?></th>
		</tr>
					<?php
					foreach ( $mv as $mv_var ) {
						?>
		<tr valign="top">
			<td><?php echo esc_html( $mv_var['name'] ); ?></td>
			<td><?php echo esc_html( $mv_var['tag'] ); ?></td>
			<td><?php echo esc_html( ( 1 === $mv_var['required'] ) ? 'Y' : 'N' ); ?></td>
			<td>
						<?php
						if ( ! $mv_var['required'] ) {
							$opt = 'mc_mv_' . $mv_var['tag'];
							?>
						<label class="screen-reader-text" for="<?php echo esc_attr( $opt ); ?>">
							<?php
							echo esc_html(
								sprintf(
									// translators: placeholder is name of field
									__( 'Include merge field %s?', 'mailchimp_i18n' ),
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
	<input type="submit" value="<?php esc_attr_e( 'Update Subscribe Form Settings', 'mailchimp_i18n' ); ?>" class="button mc-submit" /><br/>
</div>

					<?php
					// Interest Groups Table
					$igs = get_option( 'mc_interest_groups' );
					if ( is_array( $igs ) && ! isset( $igs['id'] ) ) {
						?>
		<div class="mc-section">
			<h3 class="mc-h3"><?php esc_html_e( 'Group Settings', 'mailchimp_i18n' ); ?></h3>
		</div>
						<?php
						// Determines whether or not to continue processing. Only false if there was an error.
						$continue = true;
						foreach ( $igs as $ig ) {
							if ( $continue ) {
								if ( ! is_array( $ig ) || empty( $ig ) || 'N' === $ig ) {
									?>
									<em><?php esc_html_e( 'No Interest Groups Setup for this List', 'mailchimp_i18n' ); ?></em>
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
												<label for="<?php echo esc_attr( 'mc_show_interest_groups_' . $ig['id'] ); ?>"><?php esc_html_e( 'Show?', 'mailchimp_i18n' ); ?></label>
											</th>
											<td>
												<input name="<?php echo esc_attr( 'mc_show_interest_groups_' . $ig['id'] ); ?>" id="<?php echo esc_attr( 'mc_show_interest_groups_' . $ig['id'] ); ?>" type="checkbox" class="code"<?php checked( 'on', get_option( 'mc_show_interest_groups_' . $ig['id'] ) ); ?> />
											</td>
										</tr>
										<tr valign="top">
											<th><?php esc_html_e( 'Input Type', 'mailchimp_i18n' ); ?></th>
											<td><?php echo esc_html( $ig['type'] ); ?></td>
										</tr>
										<tr valign="top" class="last-row">
											<th><?php esc_html_e( 'Options', 'mailchimp_i18n' ); ?></th>
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
				<div class="mc-section" style="margin-top: 35px;">
					<table class="widefat mc-widefat">
						<tr><th colspan="2">CSS Cheat Sheet</th></tr>
						<tr valign="top">
							<th scope="row">.widget_mailchimpsf_widget </th>
							<td>This targets the entire widget container.</td>
						</tr>
						<tr valign="top">
							<th scope="row">.widget-title</th>
							<td>This styles the title of your Mailchimp widget. <i>Modifying this class will affect your other widget titles.</i></td>
						</tr>
						<tr valign="top">
							<th scope="row">#mc_signup</th>
							<td>This targets the entirity of the widget beneath the widget title.</td>
						</tr>
						<tr valign="top">
							<th scope="row">#mc_subheader</th>
							<td>This styles the subheader text.</td>
						</tr>
						<tr valign="top">
							<th scope="row">.mc_form_inside</th>
							<td>The guts and main container for the all of the form elements (the entirety of the widget minus the header and the sub header).</td>
						</tr>
						<tr valign="top">
							<th scope="row">.mc_header</th>
							<td>This targets the label above the input fields.</td>
						</tr>
						<tr valign="top">
							<th scope="row">.mc_input</th>
							<td>This attaches to the input fields.</td>
						</tr>
						<tr valign="top">
							<th scope="row">.mc_header_address</th>
							<td>This is the label above an address group.</td>
						</tr>
						<tr valign="top">
							<th scope="row">.mc_radio_label</th>
							<td>These are the labels associated with radio buttons.</td>
						</tr>
						<tr valign="top">
							<th scope="row">#mc-indicates-required</th>
							<td>This targets the “Indicates Required Field” text.</td>
						</tr>
						<tr valign="top">
							<th scope="row">#mc_signup_submit</th>
							<td>Use this to style the submit button.</td>
						</tr>
					</table>
				</div>
			</form>
		</div>
