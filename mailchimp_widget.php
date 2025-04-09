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
	// Check if we should display the form.
	if ( ! mailchimp_sf_should_display_form() ) {
		return;
	}

	$before_title  = isset( $args['before_title'] ) ? $args['before_title'] : '';
	$after_title   = isset( $args['after_title'] ) ? $args['after_title'] : '';
	$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : '';
	$after_widget  = isset( $args['after_widget'] ) ? $args['after_widget'] : '';

	$mv  = get_option( 'mc_merge_vars' );
	$igs = get_option( 'mc_interest_groups' );

	// See if we have valid Merge Vars
	if ( ! is_array( $mv ) ) {
		if ( ! empty( $before_widget ) ) {
			echo wp_kses_post( $before_widget );
		} else {
			echo '<div class="mc_container">';
		}
		?>
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
		<?php
		if ( ! empty( $after_widget ) ) {
			echo wp_kses_post( $after_widget );
		}

		if ( empty( $before_widget ) ) {
			echo '</div>';
		}

		return;
	}

	if ( ! empty( $before_widget ) ) {
		echo wp_kses_post( $before_widget );
	} else {
		echo '<div class="mc_container">';
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
	.mc_custom_border_hdr,
	#mc_subheader {
		line-height: 1.25em;
		margin-bottom: 18px;
	}
	.mc_merge_var,
	.mc_interest {
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

<div id="mc_signup">
	<form method="post" action="#mc_signup" id="mc_signup_form" class="mc_signup_form">
		<input type="hidden" id="mc_submit_type" class="mc_submit_type" name="mc_submit_type" value="html" />
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

		<div class="mc_message_wrapper" id="mc_message">
			<?php echo wp_kses_post( mailchimp_sf_frontend_msg() ); ?>
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
			echo mailchimp_form_field( $mv_var, $num_fields ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Ignoring because form field is escaped in function
		}

		// Show an explanation of the * if there's more than one field
		if ( $num_fields > 1 ) {
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

		// Add a honeypot field.
		mailchimp_sf_honeypot_field();

		$submit_text = get_option( 'mc_submit_text' );
		?>

		<div class="mc_signup_submit">
			<input type="submit" name="mc_signup_submit" class="mc_signup_submit_button" id="mc_signup_submit" value="<?php echo esc_attr( $submit_text ); ?>" class="button" />
		</div><!-- /mc_signup_submit -->

		<?php
		$user = get_option( 'mc_user' );
		if ( $user && get_option( 'mc_use_unsub_link' ) === 'on' ) {
			$api  = mailchimp_sf_get_api();
			$host = 'http://' . $api->datacenter . '.list-manage.com';
			?>
			<div id="mc_unsub_link" align="center">
				<a href="<?php echo esc_url( $host . '/unsubscribe/?u=' . $user['account_id'] . '&amp;id=' . get_option( 'mc_list_id' ) ); ?>" target="_blank"><?php esc_html_e( 'unsubscribe from list', 'mailchimp' ); ?></a>
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

	if ( empty( $before_widget ) ) {
		echo '</div>';
	}
}

/**
 * Add a hidden honeypot field
 *
 * @return void
 */
function mailchimp_sf_honeypot_field() {
	?>
	<div style="display: none; !important">
		<label for="mailchimp_sf_alt_email"><?php esc_html_e( 'Alternative Email:', 'mailchimp' ); ?></label>
		<input type="text" name="mailchimp_sf_alt_email" autocomplete="off"/>
	</div>
	<input type="hidden" class="mailchimp_sf_no_js" name="mailchimp_sf_no_js" value="1" />
	<?php
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
 * @param array  $data           Array containing informaoin about the field.
 * @param int    $num_fields     The number of fields total we'll be generating markup for. Used in calculating required text logic.
 * @param bool   $should_display Whether or not the field should be displayed.
 * @param string $label          The label for the field.
 * @return string
 */
function mailchimp_form_field( $data, $num_fields, $should_display = null, $label = '' ) {
	$html = '';
	$opt  = 'mc_mv_' . $data['tag'];
	if ( is_null( $should_display ) ) {
		$should_display = 'on' === get_option( $opt );
	}

	$label = ( ! empty( $label ) ) ? $label : $data['name'];

	// See if that var is set as required, or turned on (for display)
	if ( $data['required'] || $should_display ) {
		$label = '<label for="' . esc_attr( $opt ) . '" class="mc_var_label mc_header mc_header_' . esc_attr( $data['type'] ) . '">' . wp_kses_post( $label );
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
					esc_html__( 'January', 'mailchimp' ),
					esc_html__( 'February', 'mailchimp' ),
					esc_html__( 'March', 'mailchimp' ),
					esc_html__( 'April', 'mailchimp' ),
					esc_html__( 'May', 'mailchimp' ),
					esc_html__( 'June', 'mailchimp' ),
					esc_html__( 'July', 'mailchimp' ),
					esc_html__( 'August', 'mailchimp' ),
					esc_html__( 'September', 'mailchimp' ),
					esc_html__( 'October', 'mailchimp' ),
					esc_html__( 'November', 'mailchimp' ),
					esc_html__( 'December', 'mailchimp' ),
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

	<label for="' . esc_attr( $opt . '-addr1' ) . '" class="mc_address_label">' . esc_html__( 'Street Address', 'mailchimp' ) . '</label>
	<input type="text" size="18" value="" name="' . esc_attr( $opt . '[addr1]' ) . '" id="' . esc_attr( $opt . '-addr1' ) . '" class="mc_input" />
	<label for="' . esc_attr( $opt . '-addr2' ) . '" class="mc_address_label">' . esc_html__( 'Address Line 2', 'mailchimp' ) . '</label>
	<input type="text" size="18" value="" name="' . esc_attr( $opt . '[addr2]' ) . '" id="' . esc_attr( $opt . '-addr2' ) . '" class="mc_input" />
	<label for="' . esc_attr( $opt . '-city' ) . '" class="mc_address_label">' . esc_html__( 'City', 'mailchimp' ) . '</label>
	<input type="text" size="18" value="" name="' . esc_attr( $opt . '[city]' ) . '" id="' . esc_attr( $opt . '-city' ) . '" class="mc_input" />
	<label for="' . esc_attr( $opt . '-state' ) . '" class="mc_address_label">' . esc_html__( 'State', 'mailchimp' ) . '</label>
	<input type="text" size="18" value="" name="' . esc_attr( $opt . '[state]' ) . '" id="' . esc_attr( $opt . '-state' ) . '" class="mc_input" />
	<label for="' . esc_attr( $opt . '-zip' ) . '" class="mc_address_label">' . esc_html__( 'Zip / Postal', 'mailchimp' ) . '</label>
	<input type="text" size="18" value="" maxlength="5" name="' . esc_attr( $opt . '[zip]' ) . '" id="' . esc_attr( $opt . '-zip' ) . '" class="mc_input" />
	<label for="' . esc_attr( $opt . '-country' ) . '" class="mc_address_label">' . esc_html__( 'Country', 'mailchimp' ) . '</label>
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
			'description' => __( 'Displays a Mailchimp Subscribe box', 'mailchimp' ),
		);
		parent::__construct( 'Mailchimp_SF_Widget', __( 'Mailchimp Widget', 'mailchimp' ), $widget_ops );
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
<p>
		<?php
		echo wp_kses(
			sprintf(
				/* translators: 1: admin url */
				__(
					'Great work! Your widget is ready to go — just head <a href="%1$s">over here</a> if you\'d like to adjust your settings.',
					'mailchimp'
				),
				esc_url( admin_url( 'admin.php?page=mailchimp_sf_options' ) )
			),
			[
				'a' => [
					'href' => [],
				],
			]
		);
		?>
</p>
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
		'164' => __( 'USA', 'mailchimp' ),
		'286' => __( 'Aaland Islands', 'mailchimp' ),
		'274' => __( 'Afghanistan', 'mailchimp' ),
		'2'   => __( 'Albania', 'mailchimp' ),
		'3'   => __( 'Algeria', 'mailchimp' ),
		'178' => __( 'American Samoa', 'mailchimp' ),
		'4'   => __( 'Andorra', 'mailchimp' ),
		'5'   => __( 'Angola', 'mailchimp' ),
		'176' => __( 'Anguilla', 'mailchimp' ),
		'175' => __( 'Antigua And Barbuda', 'mailchimp' ),
		'6'   => __( 'Argentina', 'mailchimp' ),
		'7'   => __( 'Armenia', 'mailchimp' ),
		'179' => __( 'Aruba', 'mailchimp' ),
		'8'   => __( 'Australia', 'mailchimp' ),
		'9'   => __( 'Austria', 'mailchimp' ),
		'10'  => __( 'Azerbaijan', 'mailchimp' ),
		'11'  => __( 'Bahamas', 'mailchimp' ),
		'12'  => __( 'Bahrain', 'mailchimp' ),
		'13'  => __( 'Bangladesh', 'mailchimp' ),
		'14'  => __( 'Barbados', 'mailchimp' ),
		'15'  => __( 'Belarus', 'mailchimp' ),
		'16'  => __( 'Belgium', 'mailchimp' ),
		'17'  => __( 'Belize', 'mailchimp' ),
		'18'  => __( 'Benin', 'mailchimp' ),
		'19'  => __( 'Bermuda', 'mailchimp' ),
		'20'  => __( 'Bhutan', 'mailchimp' ),
		'21'  => __( 'Bolivia', 'mailchimp' ),
		'22'  => __( 'Bosnia and Herzegovina', 'mailchimp' ),
		'23'  => __( 'Botswana', 'mailchimp' ),
		'24'  => __( 'Brazil', 'mailchimp' ),
		'180' => __( 'Brunei Darussalam', 'mailchimp' ),
		'25'  => __( 'Bulgaria', 'mailchimp' ),
		'26'  => __( 'Burkina Faso', 'mailchimp' ),
		'27'  => __( 'Burundi', 'mailchimp' ),
		'28'  => __( 'Cambodia', 'mailchimp' ),
		'29'  => __( 'Cameroon', 'mailchimp' ),
		'30'  => __( 'Canada', 'mailchimp' ),
		'31'  => __( 'Cape Verde', 'mailchimp' ),
		'32'  => __( 'Cayman Islands', 'mailchimp' ),
		'33'  => __( 'Central African Republic', 'mailchimp' ),
		'34'  => __( 'Chad', 'mailchimp' ),
		'35'  => __( 'Chile', 'mailchimp' ),
		'36'  => __( 'China', 'mailchimp' ),
		'37'  => __( 'Colombia', 'mailchimp' ),
		'38'  => __( 'Congo', 'mailchimp' ),
		'183' => __( 'Cook Islands', 'mailchimp' ),
		'268' => __( 'Costa Rica', 'mailchimp' ),
		'275' => __( 'Cote D\'Ivoire', 'mailchimp' ),
		'40'  => __( 'Croatia', 'mailchimp' ),
		'276' => __( 'Cuba', 'mailchimp' ),
		'41'  => __( 'Cyprus', 'mailchimp' ),
		'42'  => __( 'Czech Republic', 'mailchimp' ),
		'43'  => __( 'Denmark', 'mailchimp' ),
		'44'  => __( 'Djibouti', 'mailchimp' ),
		'289' => __( 'Dominica', 'mailchimp' ),
		'187' => __( 'Dominican Republic', 'mailchimp' ),
		'233' => __( 'East Timor', 'mailchimp' ),
		'45'  => __( 'Ecuador', 'mailchimp' ),
		'46'  => __( 'Egypt', 'mailchimp' ),
		'47'  => __( 'El Salvador', 'mailchimp' ),
		'48'  => __( 'Equatorial Guinea', 'mailchimp' ),
		'49'  => __( 'Eritrea', 'mailchimp' ),
		'50'  => __( 'Estonia', 'mailchimp' ),
		'51'  => __( 'Ethiopia', 'mailchimp' ),
		'191' => __( 'Faroe Islands', 'mailchimp' ),
		'52'  => __( 'Fiji', 'mailchimp' ),
		'53'  => __( 'Finland', 'mailchimp' ),
		'54'  => __( 'France', 'mailchimp' ),
		'277' => __( 'French Polynesia', 'mailchimp' ),
		'59'  => __( 'Germany', 'mailchimp' ),
		'60'  => __( 'Ghana', 'mailchimp' ),
		'194' => __( 'Gibraltar', 'mailchimp' ),
		'61'  => __( 'Greece', 'mailchimp' ),
		'195' => __( 'Greenland', 'mailchimp' ),
		'192' => __( 'Grenada', 'mailchimp' ),
		'62'  => __( 'Guam', 'mailchimp' ),
		'198' => __( 'Guatemala', 'mailchimp' ),
		'270' => __( 'Guernsey', 'mailchimp' ),
		'65'  => __( 'Guyana', 'mailchimp' ),
		'200' => __( 'Haiti', 'mailchimp' ),
		'66'  => __( 'Honduras', 'mailchimp' ),
		'67'  => __( 'Hong Kong', 'mailchimp' ),
		'68'  => __( 'Hungary', 'mailchimp' ),
		'69'  => __( 'Iceland', 'mailchimp' ),
		'70'  => __( 'India', 'mailchimp' ),
		'71'  => __( 'Indonesia', 'mailchimp' ),
		'278' => __( 'Iran', 'mailchimp' ),
		'279' => __( 'Iraq', 'mailchimp' ),
		'74'  => __( 'Ireland', 'mailchimp' ),
		'75'  => __( 'Israel', 'mailchimp' ),
		'76'  => __( 'Italy', 'mailchimp' ),
		'202' => __( 'Jamaica', 'mailchimp' ),
		'78'  => __( 'Japan', 'mailchimp' ),
		'288' => __( 'Jersey  (Channel Islands)', 'mailchimp' ),
		'79'  => __( 'Jordan', 'mailchimp' ),
		'80'  => __( 'Kazakhstan', 'mailchimp' ),
		'81'  => __( 'Kenya', 'mailchimp' ),
		'82'  => __( 'Kuwait', 'mailchimp' ),
		'83'  => __( 'Kyrgyzstan', 'mailchimp' ),
		'84'  => __( 'Lao People\'s Democratic Republic', 'mailchimp' ),
		'85'  => __( 'Latvia', 'mailchimp' ),
		'86'  => __( 'Lebanon', 'mailchimp' ),
		'281' => __( 'Libya', 'mailchimp' ),
		'90'  => __( 'Liechtenstein', 'mailchimp' ),
		'91'  => __( 'Lithuania', 'mailchimp' ),
		'92'  => __( 'Luxembourg', 'mailchimp' ),
		'208' => __( 'Macau', 'mailchimp' ),
		'93'  => __( 'Macedonia', 'mailchimp' ),
		'94'  => __( 'Madagascar', 'mailchimp' ),
		'95'  => __( 'Malawi', 'mailchimp' ),
		'96'  => __( 'Malaysia', 'mailchimp' ),
		'97'  => __( 'Maldives', 'mailchimp' ),
		'98'  => __( 'Mali', 'mailchimp' ),
		'99'  => __( 'Malta', 'mailchimp' ),
		'212' => __( 'Mauritius', 'mailchimp' ),
		'101' => __( 'Mexico', 'mailchimp' ),
		'102' => __( 'Moldova, Republic of', 'mailchimp' ),
		'103' => __( 'Monaco', 'mailchimp' ),
		'104' => __( 'Mongolia', 'mailchimp' ),
		'290' => __( 'Montenegro', 'mailchimp' ),
		'105' => __( 'Morocco', 'mailchimp' ),
		'106' => __( 'Mozambique', 'mailchimp' ),
		'242' => __( 'Myanmar', 'mailchimp' ),
		'107' => __( 'Namibia', 'mailchimp' ),
		'108' => __( 'Nepal', 'mailchimp' ),
		'109' => __( 'Netherlands', 'mailchimp' ),
		'110' => __( 'Netherlands Antilles', 'mailchimp' ),
		'213' => __( 'New Caledonia', 'mailchimp' ),
		'111' => __( 'New Zealand', 'mailchimp' ),
		'112' => __( 'Nicaragua', 'mailchimp' ),
		'113' => __( 'Niger', 'mailchimp' ),
		'114' => __( 'Nigeria', 'mailchimp' ),
		'272' => __( 'North Korea', 'mailchimp' ),
		'116' => __( 'Norway', 'mailchimp' ),
		'117' => __( 'Oman', 'mailchimp' ),
		'118' => __( 'Pakistan', 'mailchimp' ),
		'222' => __( 'Palau', 'mailchimp' ),
		'282' => __( 'Palestine', 'mailchimp' ),
		'119' => __( 'Panama', 'mailchimp' ),
		'219' => __( 'Papua New Guinea', 'mailchimp' ),
		'120' => __( 'Paraguay', 'mailchimp' ),
		'121' => __( 'Peru', 'mailchimp' ),
		'122' => __( 'Philippines', 'mailchimp' ),
		'123' => __( 'Poland', 'mailchimp' ),
		'124' => __( 'Portugal', 'mailchimp' ),
		'126' => __( 'Qatar', 'mailchimp' ),
		'58'  => __( 'Republic of Georgia', 'mailchimp' ),
		'128' => __( 'Romania', 'mailchimp' ),
		'129' => __( 'Russia', 'mailchimp' ),
		'130' => __( 'Rwanda', 'mailchimp' ),
		'205' => __( 'Saint Kitts and Nevis', 'mailchimp' ),
		'206' => __( 'Saint Lucia', 'mailchimp' ),
		'132' => __( 'Samoa (Independent)', 'mailchimp' ),
		'227' => __( 'San Marino', 'mailchimp' ),
		'133' => __( 'Saudi Arabia', 'mailchimp' ),
		'134' => __( 'Senegal', 'mailchimp' ),
		'266' => __( 'Serbia', 'mailchimp' ),
		'135' => __( 'Seychelles', 'mailchimp' ),
		'137' => __( 'Singapore', 'mailchimp' ),
		'138' => __( 'Slovakia', 'mailchimp' ),
		'139' => __( 'Slovenia', 'mailchimp' ),
		'223' => __( 'Solomon Islands', 'mailchimp' ),
		'141' => __( 'South Africa', 'mailchimp' ),
		'142' => __( 'South Korea', 'mailchimp' ),
		'143' => __( 'Spain', 'mailchimp' ),
		'144' => __( 'Sri Lanka', 'mailchimp' ),
		'293' => __( 'Sudan', 'mailchimp' ),
		'146' => __( 'Suriname', 'mailchimp' ),
		'147' => __( 'Swaziland', 'mailchimp' ),
		'148' => __( 'Sweden', 'mailchimp' ),
		'149' => __( 'Switzerland', 'mailchimp' ),
		'152' => __( 'Taiwan', 'mailchimp' ),
		'153' => __( 'Tanzania', 'mailchimp' ),
		'154' => __( 'Thailand', 'mailchimp' ),
		'155' => __( 'Togo', 'mailchimp' ),
		'232' => __( 'Tonga', 'mailchimp' ),
		'234' => __( 'Trinidad and Tobago', 'mailchimp' ),
		'156' => __( 'Tunisia', 'mailchimp' ),
		'157' => __( 'Turkey', 'mailchimp' ),
		'287' => __( 'Turks &amp; Caicos Islands', 'mailchimp' ),
		'159' => __( 'Uganda', 'mailchimp' ),
		'161' => __( 'Ukraine', 'mailchimp' ),
		'162' => __( 'United Arab Emirates', 'mailchimp' ),
		'262' => __( 'United Kingdom', 'mailchimp' ),
		'163' => __( 'Uruguay', 'mailchimp' ),
		'239' => __( 'Vanuatu', 'mailchimp' ),
		'166' => __( 'Vatican City State (Holy See)', 'mailchimp' ),
		'167' => __( 'Venezuela', 'mailchimp' ),
		'168' => __( 'Vietnam', 'mailchimp' ),
		'169' => __( 'Virgin Islands (British)', 'mailchimp' ),
		'238' => __( 'Virgin Islands (U.S.)', 'mailchimp' ),
		'173' => __( 'Zambia', 'mailchimp' ),
		'174' => __( 'Zimbabwe', 'mailchimp' ),
	);
}
