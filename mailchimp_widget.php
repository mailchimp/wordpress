<?php
/**
 * Mailchimp widget
 *
 * @package Mailchimp
 */

/**
 * Displays a Mailchimp Signup Form
 *
 * @param array $args Args
 * @return void
 */
function mailchimp_sf_signup_form( $args = array() ) {

	$before_title  = isset( $args['before_title'] ) ? $args['before_title'] : '';
	$after_title   = isset( $args['after_title'] ) ? $args['after_title'] : '';
	$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : '';
	$after_widget  = isset( $args['after_widget'] ) ? $args['after_widget'] : '';

	$mv  = get_option( 'mc_merge_vars' );
	$igs = get_option( 'mc_interest_groups' );

	// See if we have valid Merge Vars
	if ( ! is_array( $mv ) ) {
		echo wp_kses_post( $before_widget );
		?>
		<div class="mc_error_msg">
			<?php
			echo wp_kses(
				__(
					'Sorry, there was a problem loading your Mailchimp details. Please navigate to <strong>Settings</strong> and click <strong>Mailchimp Setup</strong> to try again.',
					'mailchimp_i18n'
				),
				[
					'strong' => [],
				]
			);
			?>
		</div>
		<?php
		echo wp_kses_post( $after_widget );
		return;
	}

	if ( ! empty( $before_widget ) ) {
		echo wp_kses_post( $before_widget );
	}

	$header = get_option( 'mc_header_content' );

	// See if we have custom header content
	if ( ! empty( $header ) ) {
		// See if we need to wrap the header content in our own div
		if ( strlen( $header ) === strlen( wp_strip_all_tags( $header ) ) ) {
			echo ! empty( $before_title ) ? wp_kses_post( $before_title ) : '<div class="mc_custom_border_hdr">';
			echo wp_kses_post( $header );
			echo ! empty( $after_title ) ? wp_kses_post( $after_title ) : '</div><!-- /mc_custom_border_hdr -->';
		} else {
			echo wp_kses_post( $header );
		}
	}

	$sub_heading = trim( get_option( 'mc_subheader_content' ) );

	if ( get_option( 'mc_nuke_all_styles' ) !== '1' ) {
		?>
	<style>
		.widget_mailchimpsf_widget .widget-title {
		line-height: 1.4em;
		margin-bottom: 0.75em;
	}
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
		width: 100%;
	}
	.mc_input.mc_phone {
		width: auto;
	}
	select.mc_select {
		margin-top: 0.5em;
		width: 100%;
	}
	.mc_address_label {
		margin-top: 1.0em;
		margin-bottom: 0.5em;
		display: block;
	}
	.mc_address_label ~ select {
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

<div id="mc_signup">
	<form method="post" action="#mc_signup" id="mc_signup_form">
		<input type="hidden" id="mc_submit_type" name="mc_submit_type" value="html" />
		<input type="hidden" name="mcsf_action" value="mc_submit_signup_form" />
		<?php wp_nonce_field( 'mc_submit_signup_form', '_mc_submit_signup_form_nonce', false ); ?>

	<?php
	if ( $sub_heading ) {
		?>
		<div id="mc_subheader">
			<?php echo wp_kses_post( $sub_heading ); ?>
		</div><!-- /mc_subheader -->
		<?php
	}
	?>

	<div class="mc_form_inside">

		<div class="updated" id="mc_message">
			<?php echo wp_kses_post( mailchimp_sf_global_msg() ); ?>
		</div><!-- /mc_message -->

		<?php
		// don't show the "required" stuff if there's only 1 field to display.
		$num_fields = 0;
		foreach ( (array) $mv as $mv_var ) {
			$opt = 'mc_mv_' . $mv_var['tag'];
			if ( $mv_var['required'] || get_option( $opt ) === 'on' ) {
				++$num_fields;
			}
		}

		if ( is_array( $mv ) ) {
			// head on back to the beginning of the array
			reset( $mv );
		}

		// Loop over our vars, and output the ones that are set to display
		foreach ( $mv as $mv_var ) {
			if ( ! $mv_var['public'] ) {
				echo '<div style="display:none;">' . mailchimp_form_field( $mv_var, $num_fields ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Ignoring because form field is escaped in function
			} else {
				echo mailchimp_form_field( $mv_var, $num_fields ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Ignoring because form field is escaped in function
			}
		}

		// Show an explanation of the * if there's more than one field
		if ( $num_fields > 1 ) {
			?>
			<div id="mc-indicates-required">
				* = <?php esc_html_e( 'required field', 'mailchimp_i18n' ); ?>
			</div><!-- /mc-indicates-required -->
			<?php
		}

		// Show our Interest groups fields if we have them, and they're set to on
		if ( is_array( $igs ) && ! empty( $igs ) ) {
			foreach ( $igs as $ig ) {
				if ( is_array( $ig ) && isset( $ig['id'] ) ) {
					if ( ( $igs && get_option( 'mc_show_interest_groups_' . $ig['id'] ) === 'on' ) ) {
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

		if ( get_option( 'mc_email_type_option' ) ) {
			?>
		<div class="mergeRow">
			<label class="mc_email_format"><?php esc_html_e( 'Preferred Format', 'mailchimp_i18n' ); ?></label>
			<div class="field-group groups mc_email_options">
				<ul class="mc_list">
					<li><input type="radio" name="email_type" id="email_type_html" value="html" checked="checked"><label for="email_type_html" class="mc_email_type"><?php esc_html_e( 'HTML', 'mailchimp_i18n' ); ?></label></li>
					<li><input type="radio" name="email_type" id="email_type_text" value="text"><label for="email_type_text" class="mc_email_type"><?php esc_html_e( 'Text', 'mailchimp_i18n' ); ?></label></li>
				</ul>
			</div>
		</div>

			<?php
		}

		$submit_text = get_option( 'mc_submit_text' );

		?>

		<div class="mc_signup_submit">
			<input type="submit" name="mc_signup_submit" id="mc_signup_submit" value="<?php echo esc_attr( $submit_text ); ?>" class="button" />
		</div><!-- /mc_signup_submit -->

		<?php
		$user = get_option( 'mc_user' );
		if ( $user && get_option( 'mc_use_unsub_link' ) === 'on' ) {
			$api  = mailchimp_sf_get_api();
			$host = 'http://' . $api->datacenter . '.list-manage.com';
			?>
			<div id="mc_unsub_link" align="center">
				<a href="<?php echo esc_url( $host . '/unsubscribe/?u=' . $user['account_id'] . '&amp;id=' . get_option( 'mc_list_id' ) ); ?>" target="_blank"><?php esc_html_e( 'unsubscribe from list', 'mailchimp_i18n' ); ?></a>
			</div><!-- /mc_unsub_link -->
			<?php
		}
		?>

	</div><!-- /mc_form_inside -->
	</form><!-- /mc_signup_form -->
</div><!-- /mc_signup_container -->
	<?php
	if ( ! empty( $after_widget ) ) {
		echo wp_kses_post( $after_widget );
	}
}

/**
 * Generate and display markup for Interest Groups
 *
 * @param array $ig Set of Interest Groups to generate markup for
 * @return void
 */
function mailchimp_interest_group_field( $ig ) {
	if ( ! is_array( $ig ) ) {
		return;
	}
	$html     = '';
	$set_name = 'group[' . $ig['id'] . ']';
	switch ( $ig['type'] ) {
		case 'checkboxes':
			$i = 1;
			foreach ( $ig['groups'] as $interest ) {
				$interest_name = $interest['name'];
				$interest_id   = $interest['id'];
				$html         .= '

				<input type="checkbox" name="' . esc_attr( $set_name . '[' . $interest_id . ']' ) . '" id="' . esc_attr( 'mc_interest_' . $ig['id'] . '_' . $interest_id ) . '" class="mc_interest" value="' . esc_attr( $interest_name ) . '" />
				<label for="' . esc_attr( 'mc_interest_' . $ig['id'] . '_' . $interest_id ) . '" class="mc_interest_label">' . esc_html( $interest_name ) . '</label>
				<br/>';
				++$i;
			}
			break;
		case 'radio':
			foreach ( $ig['groups'] as $interest ) {
				$interest_name = $interest['name'];
				$interest_id   = $interest['id'];
				$html         .= '
				<input type="radio" name="' . esc_attr( $set_name ) . '" id="' . esc_attr( 'mc_interest_' . $ig['id'] . '_' . $interest_id ) . '" class="mc_interest" value="' . esc_attr( $interest_id ) . '"/>
				<label for="' . esc_attr( 'mc_interest_' . $ig['id'] . '_' . $interest_id ) . '" class="mc_interest_label">' . esc_html( $interest_name ) . '</label>
				<br/>';
			}
			break;
		case 'dropdown':
			$html .= '
			<select name="' . esc_attr( $set_name ) . '">
				<option value=""></option>';
			foreach ( $ig['groups'] as $interest ) {
				$interest_name = $interest['name'];
				$interest_id   = $interest['id'];
				$html         .= '
				<option value="' . esc_attr( $interest_id ) . '">' . esc_html( $interest_name ) . '</option>';
			}
				$html .= '
			</select>';
			break;
		case 'hidden':
			$i = 1;
			foreach ( $ig['groups'] as $interest ) {
				$interest_name = $interest['name'];
				$interest_id   = $interest['id'];
				$html         .= '
				<input type="checkbox" name="' . esc_attr( $set_name . '[' . $i . ']' ) . '" id="' . esc_attr( 'mc_interest_' . $ig['id'] . '_' . $interest_id ) . '" class="mc_interest" value="' . esc_attr( $interest_name ) . '" />
				<label for="' . esc_attr( 'mc_interest_' . $ig['id'] . '_' . $interest_id ) . '" class="mc_interest_label">' . esc_html( $interest_name ) . '</label>';
				++$i;
			}
			break;
	}
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Ignoring because html is previously escaped
}

/**
 * Generate and display markup for form fields
 *
 * @param array $data Array containing informaoin about the field
 * @param int   $num_fields The number of fields total we'll be generating markup for. Used in calculating required text logic
 * @return string
 */
function mailchimp_form_field( $data, $num_fields ) {
	$opt  = 'mc_mv_' . $data['tag'];
	$html = '';
	// See if that var is set as required, or turned on (for display)
	if ( $data['required'] || get_option( $opt ) === 'on' ) {
		$label = '<label for="' . esc_attr( $opt ) . '" class="mc_var_label mc_header mc_header_' . esc_attr( $data['type'] ) . '">' . esc_html( $data['name'] );
		if ( $data['required'] && $num_fields > 1 ) {
			$label .= '<span class="mc_required">*</span>';
		}
		$label .= '</label>';

		$html .= '
<div class="mc_merge_var">
		' . $label;
		switch ( $data['type'] ) {
			case 'date':
				$html .= '
	<input type="text" size="18" placeholder="' . esc_attr( $data['default_value'] ) . '" data-format="' . esc_attr( $data['options']['date_format'] ) . '" name="' . esc_attr( $opt ) . '" id="' . esc_attr( $opt ) . '" class="date-pick mc_input"/>';
				break;
			case 'radio':
				if ( is_array( $data['options']['choices'] ) ) {
					$html .= '
	<ul class="mc_list">';
					foreach ( $data['options']['choices'] as $key => $value ) {
						$html .= '
		<li>
			<input type="radio" id="' . esc_attr( $opt . '_' . $key ) . '" name="' . esc_attr( $opt ) . '" class="mc_radio" value="' . $value . '"' . checked( $data['default_value'], $value, false ) . ' />
			<label for="' . esc_attr( $opt . '_' . $key ) . '" class="mc_radio_label">' . esc_html( $value ) . '</label>
		</li>';
					}
					$html .= '
	</ul>';
				}
				break;
			case 'dropdown':
				if ( is_array( $data['options']['choices'] ) ) {
					$html .= '
		<select id="' . esc_attr( $opt ) . '" name="' . esc_attr( $opt ) . '" class="mc_select">';
					foreach ( $data['options']['choices'] as $value ) {
						$html .= '
		<option value="' . esc_attr( $value ) . '"' . selected( $value, $data['default_value'], false ) . '>' . esc_html( $value ) . '</option>';
					}
					$html .= '
	</select>';
				}
				break;
			case 'birthday':
				$html .= '
	<input type="text" size="18" placeholder="' . esc_attr( $data['default_value'] ) . '" data-format="' . esc_attr( $data['options']['date_format'] ) . '" name="' . esc_attr( $opt ) . '" id="' . esc_attr( $opt ) . '" class="birthdate-pick mc_input"/>';
				break;
			case 'birthday-old':
				$days   = range( 1, 31 );
				$months = array(
					__( 'January', 'mailchimp_i18n' ),
					__( 'February', 'mailchimp_i18n' ),
					__( 'March', 'mailchimp_i18n' ),
					__( 'April', 'mailchimp_i18n' ),
					__( 'May', 'mailchimp_i18n' ),
					__( 'June', 'mailchimp_i18n' ),
					__( 'July', 'mailchimp_i18n' ),
					__( 'August', 'mailchimp_i18n' ),
					__( 'September', 'mailchimp_i18n' ),
					__( 'October', 'mailchimp_i18n' ),
					__( 'November', 'mailchimp_i18n' ),
					__( 'December', 'mailchimp_i18n' ),
				);

				$html .= '
	<br /><select id="' . esc_attr( $opt ) . '" name="' . esc_attr( $opt . '[month]' ) . '" class="mc_select">';
				foreach ( $months as $month_key => $month ) {
					$html .= '
		<option value="' . $month_key . '">' . $month . '</option>';
				}
				$html .= '
	</select>';

				$html .= '
	<select id="' . esc_attr( $opt ) . '" name="' . esc_attr( $opt . '[day]' ) . '" class="mc_select">';
				foreach ( $days as $day ) {
					$html .= '
		<option value="' . $day . '">' . $day . '</option>';
				}
				$html .= '
	</select>';
				break;
			case 'address':
				$countries = mailchimp_country_list();
				$html     .= '

	<label for="' . esc_attr( $opt . '-addr1' ) . '" class="mc_address_label">' . __( 'Street Address', 'mailchimp_i18n' ) . '</label>
	<input type="text" size="18" value="" name="' . esc_attr( $opt . '[addr1]' ) . '" id="' . esc_attr( $opt . '-addr1' ) . '" class="mc_input" />
	<label for="' . esc_attr( $opt . '-addr2' ) . '" class="mc_address_label">' . __( 'Address Line 2', 'mailchimp_i18n' ) . '</label>
	<input type="text" size="18" value="" name="' . esc_attr( $opt . '[addr2]' ) . '" id="' . esc_attr( $opt . '-addr2' ) . '" class="mc_input" />
	<label for="' . esc_attr( $opt . '-city' ) . '" class="mc_address_label">' . __( 'City', 'mailchimp_i18n' ) . '</label>
	<input type="text" size="18" value="" name="' . esc_attr( $opt . '[city]' ) . '" id="' . esc_attr( $opt . '-city' ) . '" class="mc_input" />
	<label for="' . esc_attr( $opt . '-state' ) . '" class="mc_address_label">' . __( 'State', 'mailchimp_i18n' ) . '</label>
	<input type="text" size="18" value="" name="' . esc_attr( $opt . '[state]' ) . '" id="' . esc_attr( $opt . '-state' ) . '" class="mc_input" />
	<label for="' . esc_attr( $opt . '-zip' ) . '" class="mc_address_label">' . __( 'Zip / Postal', 'mailchimp_i18n' ) . '</label>
	<input type="text" size="18" value="" maxlength="5" name="' . esc_attr( $opt . '[zip]' ) . '" id="' . esc_attr( $opt . '-zip' ) . '" class="mc_input" />
	<label for="' . esc_attr( $opt . '-country' ) . '" class="mc_address_label">' . __( 'Country', 'mailchimp_i18n' ) . '</label>
	<select name="' . esc_attr( $opt . '[country]' ) . '" id="' . esc_attr( $opt . '-country' ) . '">';
				foreach ( $countries as $country_code => $country_name ) {
					$html .= '
		<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $data['options']['default_country'], false ) . '>' . esc_html( $country_name ) . '</option>';
				}
				$html .= '
	</select>';
				break;
			case 'zip':
				$html .= '
	<input type="text" size="18" maxlength="5" value="" name="' . esc_attr( $opt ) . '" id="' . esc_attr( $opt ) . '" class="mc_input" />';
				break;
			case 'phone':
				if ( isset( $data['options']['phone_format'] ) && 'US' === $data['options']['phone_format'] ) {
					$html .= '
			<input type="text" size="2" maxlength="3" value="" name="' . esc_attr( $opt . '[area]' ) . '" id="' . esc_attr( $opt . '-area' ) . '" class="mc_input mc_phone" />
			<input type="text" size="2" maxlength="3" value="" name="' . esc_attr( $opt . '[detail1]' ) . '" id="' . esc_attr( $opt . '-detail1' ) . '" class="mc_input mc_phone" />
			<input type="text" size="5" maxlength="4" value="" name="' . esc_attr( $opt . '[detail2]' ) . '" id="' . esc_attr( $opt . '-detail2' ) . '" class="mc_input mc_phone" />
				';
				} else {
					$html .= '
						<input type="text" size="18" value="" name="' . esc_attr( $opt ) . '" id="' . esc_attr( $opt ) . '" class="mc_input" />
					';
				}
				break;
			case 'email':
			case 'url':
			case 'imageurl':
			case 'text':
			case 'number':
			default:
				$html .= '
	<input type="text" size="18" placeholder="' . esc_attr( $data['default_value'] ) . '" name="' . esc_attr( $opt ) . '" id="' . esc_attr( $opt ) . '" class="mc_input"/>';
				break;
		}
		if ( ! empty( $data['help_text'] ) ) {
			$html .= '<span class="mc_help">' . esc_html( $data['help_text'] ) . '</span>';
		}
		$html .= '
</div><!-- /mc_merge_var -->';
	}

	return $html;
}

/**
 * Mailchimp Subscribe Box widget class
 */
class Mailchimp_SF_Widget extends WP_Widget /* phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed */ {

	/**
	 * Initialize class
	 */
	public function __construct() {
		$widget_ops = array(
			'description' => __( 'Displays a Mailchimp Subscribe box', 'mailchimp_i18n' ),
		);
		parent::__construct( 'Mailchimp_SF_Widget', __( 'Mailchimp Widget', 'mailchimp_i18n' ), $widget_ops );
	}

	/**
	 * Echoes the widget content.
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		if ( ! is_array( $instance ) ) {
			$instance = array();
		}
		mailchimp_sf_signup_form( array_merge( $args, $instance ) );
	}

	/**
	 * Outputs the settings update form.
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		?>
<p>Great work! Your widget is ready to go — just head <a href="<?php echo esc_url( admin_url( 'options-general.php?page=mailchimp_sf_options' ) ); ?>">over here</a> if you'd like to adjust your settings.</p>
		<?php
	}
}

/**
 * Return country list
 *
 * @return array
 */
function mailchimp_country_list() {
	return array(
		'164' => __( 'USA', 'mailchimp_i18n' ),
		'286' => __( 'Aaland Islands', 'mailchimp_i18n' ),
		'274' => __( 'Afghanistan', 'mailchimp_i18n' ),
		'2'   => __( 'Albania', 'mailchimp_i18n' ),
		'3'   => __( 'Algeria', 'mailchimp_i18n' ),
		'178' => __( 'American Samoa', 'mailchimp_i18n' ),
		'4'   => __( 'Andorra', 'mailchimp_i18n' ),
		'5'   => __( 'Angola', 'mailchimp_i18n' ),
		'176' => __( 'Anguilla', 'mailchimp_i18n' ),
		'175' => __( 'Antigua And Barbuda', 'mailchimp_i18n' ),
		'6'   => __( 'Argentina', 'mailchimp_i18n' ),
		'7'   => __( 'Armenia', 'mailchimp_i18n' ),
		'179' => __( 'Aruba', 'mailchimp_i18n' ),
		'8'   => __( 'Australia', 'mailchimp_i18n' ),
		'9'   => __( 'Austria', 'mailchimp_i18n' ),
		'10'  => __( 'Azerbaijan', 'mailchimp_i18n' ),
		'11'  => __( 'Bahamas', 'mailchimp_i18n' ),
		'12'  => __( 'Bahrain', 'mailchimp_i18n' ),
		'13'  => __( 'Bangladesh', 'mailchimp_i18n' ),
		'14'  => __( 'Barbados', 'mailchimp_i18n' ),
		'15'  => __( 'Belarus', 'mailchimp_i18n' ),
		'16'  => __( 'Belgium', 'mailchimp_i18n' ),
		'17'  => __( 'Belize', 'mailchimp_i18n' ),
		'18'  => __( 'Benin', 'mailchimp_i18n' ),
		'19'  => __( 'Bermuda', 'mailchimp_i18n' ),
		'20'  => __( 'Bhutan', 'mailchimp_i18n' ),
		'21'  => __( 'Bolivia', 'mailchimp_i18n' ),
		'22'  => __( 'Bosnia and Herzegovina', 'mailchimp_i18n' ),
		'23'  => __( 'Botswana', 'mailchimp_i18n' ),
		'24'  => __( 'Brazil', 'mailchimp_i18n' ),
		'180' => __( 'Brunei Darussalam', 'mailchimp_i18n' ),
		'25'  => __( 'Bulgaria', 'mailchimp_i18n' ),
		'26'  => __( 'Burkina Faso', 'mailchimp_i18n' ),
		'27'  => __( 'Burundi', 'mailchimp_i18n' ),
		'28'  => __( 'Cambodia', 'mailchimp_i18n' ),
		'29'  => __( 'Cameroon', 'mailchimp_i18n' ),
		'30'  => __( 'Canada', 'mailchimp_i18n' ),
		'31'  => __( 'Cape Verde', 'mailchimp_i18n' ),
		'32'  => __( 'Cayman Islands', 'mailchimp_i18n' ),
		'33'  => __( 'Central African Republic', 'mailchimp_i18n' ),
		'34'  => __( 'Chad', 'mailchimp_i18n' ),
		'35'  => __( 'Chile', 'mailchimp_i18n' ),
		'36'  => __( 'China', 'mailchimp_i18n' ),
		'37'  => __( 'Colombia', 'mailchimp_i18n' ),
		'38'  => __( 'Congo', 'mailchimp_i18n' ),
		'183' => __( 'Cook Islands', 'mailchimp_i18n' ),
		'268' => __( 'Costa Rica', 'mailchimp_i18n' ),
		'275' => __( 'Cote D\'Ivoire', 'mailchimp_i18n' ),
		'40'  => __( 'Croatia', 'mailchimp_i18n' ),
		'276' => __( 'Cuba', 'mailchimp_i18n' ),
		'41'  => __( 'Cyprus', 'mailchimp_i18n' ),
		'42'  => __( 'Czech Republic', 'mailchimp_i18n' ),
		'43'  => __( 'Denmark', 'mailchimp_i18n' ),
		'44'  => __( 'Djibouti', 'mailchimp_i18n' ),
		'289' => __( 'Dominica', 'mailchimp_i18n' ),
		'187' => __( 'Dominican Republic', 'mailchimp_i18n' ),
		'233' => __( 'East Timor', 'mailchimp_i18n' ),
		'45'  => __( 'Ecuador', 'mailchimp_i18n' ),
		'46'  => __( 'Egypt', 'mailchimp_i18n' ),
		'47'  => __( 'El Salvador', 'mailchimp_i18n' ),
		'48'  => __( 'Equatorial Guinea', 'mailchimp_i18n' ),
		'49'  => __( 'Eritrea', 'mailchimp_i18n' ),
		'50'  => __( 'Estonia', 'mailchimp_i18n' ),
		'51'  => __( 'Ethiopia', 'mailchimp_i18n' ),
		'191' => __( 'Faroe Islands', 'mailchimp_i18n' ),
		'52'  => __( 'Fiji', 'mailchimp_i18n' ),
		'53'  => __( 'Finland', 'mailchimp_i18n' ),
		'54'  => __( 'France', 'mailchimp_i18n' ),
		'277' => __( 'French Polynesia', 'mailchimp_i18n' ),
		'59'  => __( 'Germany', 'mailchimp_i18n' ),
		'60'  => __( 'Ghana', 'mailchimp_i18n' ),
		'194' => __( 'Gibraltar', 'mailchimp_i18n' ),
		'61'  => __( 'Greece', 'mailchimp_i18n' ),
		'195' => __( 'Greenland', 'mailchimp_i18n' ),
		'192' => __( 'Grenada', 'mailchimp_i18n' ),
		'62'  => __( 'Guam', 'mailchimp_i18n' ),
		'198' => __( 'Guatemala', 'mailchimp_i18n' ),
		'270' => __( 'Guernsey', 'mailchimp_i18n' ),
		'65'  => __( 'Guyana', 'mailchimp_i18n' ),
		'200' => __( 'Haiti', 'mailchimp_i18n' ),
		'66'  => __( 'Honduras', 'mailchimp_i18n' ),
		'67'  => __( 'Hong Kong', 'mailchimp_i18n' ),
		'68'  => __( 'Hungary', 'mailchimp_i18n' ),
		'69'  => __( 'Iceland', 'mailchimp_i18n' ),
		'70'  => __( 'India', 'mailchimp_i18n' ),
		'71'  => __( 'Indonesia', 'mailchimp_i18n' ),
		'278' => __( 'Iran', 'mailchimp_i18n' ),
		'279' => __( 'Iraq', 'mailchimp_i18n' ),
		'74'  => __( 'Ireland', 'mailchimp_i18n' ),
		'75'  => __( 'Israel', 'mailchimp_i18n' ),
		'76'  => __( 'Italy', 'mailchimp_i18n' ),
		'202' => __( 'Jamaica', 'mailchimp_i18n' ),
		'78'  => __( 'Japan', 'mailchimp_i18n' ),
		'288' => __( 'Jersey  (Channel Islands)', 'mailchimp_i18n' ),
		'79'  => __( 'Jordan', 'mailchimp_i18n' ),
		'80'  => __( 'Kazakhstan', 'mailchimp_i18n' ),
		'81'  => __( 'Kenya', 'mailchimp_i18n' ),
		'82'  => __( 'Kuwait', 'mailchimp_i18n' ),
		'83'  => __( 'Kyrgyzstan', 'mailchimp_i18n' ),
		'84'  => __( 'Lao People\'s Democratic Republic', 'mailchimp_i18n' ),
		'85'  => __( 'Latvia', 'mailchimp_i18n' ),
		'86'  => __( 'Lebanon', 'mailchimp_i18n' ),
		'281' => __( 'Libya', 'mailchimp_i18n' ),
		'90'  => __( 'Liechtenstein', 'mailchimp_i18n' ),
		'91'  => __( 'Lithuania', 'mailchimp_i18n' ),
		'92'  => __( 'Luxembourg', 'mailchimp_i18n' ),
		'208' => __( 'Macau', 'mailchimp_i18n' ),
		'93'  => __( 'Macedonia', 'mailchimp_i18n' ),
		'94'  => __( 'Madagascar', 'mailchimp_i18n' ),
		'95'  => __( 'Malawi', 'mailchimp_i18n' ),
		'96'  => __( 'Malaysia', 'mailchimp_i18n' ),
		'97'  => __( 'Maldives', 'mailchimp_i18n' ),
		'98'  => __( 'Mali', 'mailchimp_i18n' ),
		'99'  => __( 'Malta', 'mailchimp_i18n' ),
		'212' => __( 'Mauritius', 'mailchimp_i18n' ),
		'101' => __( 'Mexico', 'mailchimp_i18n' ),
		'102' => __( 'Moldova, Republic of', 'mailchimp_i18n' ),
		'103' => __( 'Monaco', 'mailchimp_i18n' ),
		'104' => __( 'Mongolia', 'mailchimp_i18n' ),
		'290' => __( 'Montenegro', 'mailchimp_i18n' ),
		'105' => __( 'Morocco', 'mailchimp_i18n' ),
		'106' => __( 'Mozambique', 'mailchimp_i18n' ),
		'242' => __( 'Myanmar', 'mailchimp_i18n' ),
		'107' => __( 'Namibia', 'mailchimp_i18n' ),
		'108' => __( 'Nepal', 'mailchimp_i18n' ),
		'109' => __( 'Netherlands', 'mailchimp_i18n' ),
		'110' => __( 'Netherlands Antilles', 'mailchimp_i18n' ),
		'213' => __( 'New Caledonia', 'mailchimp_i18n' ),
		'111' => __( 'New Zealand', 'mailchimp_i18n' ),
		'112' => __( 'Nicaragua', 'mailchimp_i18n' ),
		'113' => __( 'Niger', 'mailchimp_i18n' ),
		'114' => __( 'Nigeria', 'mailchimp_i18n' ),
		'272' => __( 'North Korea', 'mailchimp_i18n' ),
		'116' => __( 'Norway', 'mailchimp_i18n' ),
		'117' => __( 'Oman', 'mailchimp_i18n' ),
		'118' => __( 'Pakistan', 'mailchimp_i18n' ),
		'222' => __( 'Palau', 'mailchimp_i18n' ),
		'282' => __( 'Palestine', 'mailchimp_i18n' ),
		'119' => __( 'Panama', 'mailchimp_i18n' ),
		'219' => __( 'Papua New Guinea', 'mailchimp_i18n' ),
		'120' => __( 'Paraguay', 'mailchimp_i18n' ),
		'121' => __( 'Peru', 'mailchimp_i18n' ),
		'122' => __( 'Philippines', 'mailchimp_i18n' ),
		'123' => __( 'Poland', 'mailchimp_i18n' ),
		'124' => __( 'Portugal', 'mailchimp_i18n' ),
		'126' => __( 'Qatar', 'mailchimp_i18n' ),
		'58'  => __( 'Republic of Georgia', 'mailchimp_i18n' ),
		'128' => __( 'Romania', 'mailchimp_i18n' ),
		'129' => __( 'Russia', 'mailchimp_i18n' ),
		'130' => __( 'Rwanda', 'mailchimp_i18n' ),
		'205' => __( 'Saint Kitts and Nevis', 'mailchimp_i18n' ),
		'206' => __( 'Saint Lucia', 'mailchimp_i18n' ),
		'132' => __( 'Samoa (Independent)', 'mailchimp_i18n' ),
		'227' => __( 'San Marino', 'mailchimp_i18n' ),
		'133' => __( 'Saudi Arabia', 'mailchimp_i18n' ),
		'134' => __( 'Senegal', 'mailchimp_i18n' ),
		'266' => __( 'Serbia', 'mailchimp_i18n' ),
		'135' => __( 'Seychelles', 'mailchimp_i18n' ),
		'137' => __( 'Singapore', 'mailchimp_i18n' ),
		'138' => __( 'Slovakia', 'mailchimp_i18n' ),
		'139' => __( 'Slovenia', 'mailchimp_i18n' ),
		'223' => __( 'Solomon Islands', 'mailchimp_i18n' ),
		'141' => __( 'South Africa', 'mailchimp_i18n' ),
		'142' => __( 'South Korea', 'mailchimp_i18n' ),
		'143' => __( 'Spain', 'mailchimp_i18n' ),
		'144' => __( 'Sri Lanka', 'mailchimp_i18n' ),
		'293' => __( 'Sudan', 'mailchimp_i18n' ),
		'146' => __( 'Suriname', 'mailchimp_i18n' ),
		'147' => __( 'Swaziland', 'mailchimp_i18n' ),
		'148' => __( 'Sweden', 'mailchimp_i18n' ),
		'149' => __( 'Switzerland', 'mailchimp_i18n' ),
		'152' => __( 'Taiwan', 'mailchimp_i18n' ),
		'153' => __( 'Tanzania', 'mailchimp_i18n' ),
		'154' => __( 'Thailand', 'mailchimp_i18n' ),
		'155' => __( 'Togo', 'mailchimp_i18n' ),
		'232' => __( 'Tonga', 'mailchimp_i18n' ),
		'234' => __( 'Trinidad and Tobago', 'mailchimp_i18n' ),
		'156' => __( 'Tunisia', 'mailchimp_i18n' ),
		'157' => __( 'Turkey', 'mailchimp_i18n' ),
		'287' => __( 'Turks &amp; Caicos Islands', 'mailchimp_i18n' ),
		'159' => __( 'Uganda', 'mailchimp_i18n' ),
		'161' => __( 'Ukraine', 'mailchimp_i18n' ),
		'162' => __( 'United Arab Emirates', 'mailchimp_i18n' ),
		'262' => __( 'United Kingdom', 'mailchimp_i18n' ),
		'163' => __( 'Uruguay', 'mailchimp_i18n' ),
		'239' => __( 'Vanuatu', 'mailchimp_i18n' ),
		'166' => __( 'Vatican City State (Holy See)', 'mailchimp_i18n' ),
		'167' => __( 'Venezuela', 'mailchimp_i18n' ),
		'168' => __( 'Vietnam', 'mailchimp_i18n' ),
		'169' => __( 'Virgin Islands (British)', 'mailchimp_i18n' ),
		'238' => __( 'Virgin Islands (U.S.)', 'mailchimp_i18n' ),
		'173' => __( 'Zambia', 'mailchimp_i18n' ),
		'174' => __( 'Zimbabwe', 'mailchimp_i18n' ),
	);
}
