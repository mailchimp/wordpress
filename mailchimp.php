<?php
/**
 * Plugin Name:       Mailchimp
 * Plugin URI:        https://mailchimp.com/help/connect-or-disconnect-list-subscribe-for-wordpress/
 * Description:       Add a Mailchimp signup form widget to your WordPress site.
 * Version:           1.5.8
 * Requires at least: 2.8
 * Requires PHP:      7.0
 * PHP tested up to:  8.3
 * Author:            Mailchimp
 * Author URI:        https://mailchimp.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html
 *
 * @package Mailchimp
 **/

/**
 * Copyright 2008-2012  Mailchimp.com  (email : api@mailchimp.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Version constant for easy CSS refreshes
define( 'MCSF_VER', '1.5.8' );

// What's our permission (capability) threshold
define( 'MCSF_CAP_THRESHOLD', 'manage_options' );

// Define our location constants, both MCSF_DIR and MCSF_URL
mailchimp_sf_where_am_i();

// Get our Mailchimp API class in scope
if ( ! class_exists( 'MailChimp_API' ) ) {
	$path = plugin_dir_path( __FILE__ );
	require_once $path . 'lib/mailchimp/mailchimp.php';
}

// includes the widget code so it can be easily called either normally or via ajax
require_once 'mailchimp_widget.php';

// includes the backwards compatibility functions
require_once 'mailchimp_compat.php';

/**
 * Do the following plugin setup steps here
 *
 * Internationalization
 * Resource (JS & CSS) enqueuing
 *
 * @return void
 */
function mailchimp_sf_plugin_init() {
	// Internationalize the plugin
	$textdomain = 'mailchimp_i18n';
	$locale     = apply_filters( 'plugin_locale', get_locale(), $textdomain );
	load_textdomain( 'mailchimp_i18n', MCSF_LANG_DIR . $textdomain . '-' . $locale . '.mo' );

	// Remove Sopresto check. If user does not have API key, make them authenticate.

	if ( get_option( 'mc_list_id' ) && get_option( 'mc_merge_field_migrate' ) !== '1' && mailchimp_sf_get_api() !== false ) {
		mailchimp_sf_update_merge_fields();
	}

	// Bring in our appropriate JS and CSS resources
	mailchimp_sf_load_resources();
}

add_action( 'init', 'mailchimp_sf_plugin_init' );

/**
 * Add the settings link to the Mailchimp plugin row
 *
 * @param array $links - Links for the plugin
 * @return array - Links
 */
function mailchimp_sf_plugin_action_links( $links ) {
	$settings_page = add_query_arg( array( 'page' => 'mailchimp_sf_options' ), admin_url( 'options-general.php' ) );
	$settings_link = '<a href="' . esc_url( $settings_page ) . '">' . __( 'Settings', 'mailchimp_i18n' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'mailchimp_sf_plugin_action_links', 10, 1 );

/**
 * Loads the appropriate JS and CSS resources depending on
 * settings and context (admin or not)
 *
 * @return void
 */
function mailchimp_sf_load_resources() {
	// JS
	if ( get_option( 'mc_use_javascript' ) === 'on' ) {
		if ( ! is_admin() ) {
			wp_enqueue_script( 'jquery_scrollto', MCSF_URL . 'js/scrollTo.js', array( 'jquery' ), MCSF_VER, true );
			wp_enqueue_script( 'mailchimp_sf_main_js', MCSF_URL . 'js/mailchimp.js', array( 'jquery', 'jquery-form' ), MCSF_VER, true );
			// some javascript to get ajax version submitting to the proper location
			global $wp_scripts;
			$wp_scripts->localize(
				'mailchimp_sf_main_js',
				'mailchimpSF',
				array(
					'ajax_url' => trailingslashit( home_url() ),
				)
			);
		}
	}

	if ( get_option( 'mc_use_datepicker' ) === 'on' && ! is_admin() ) {
		// Datepicker theme
		wp_enqueue_style( 'flick', MCSF_URL . 'css/flick/flick.css', array(), MCSF_VER );

		// Datepicker JS
		wp_enqueue_script( 'jquery-ui-datepicker' );
	}

	if ( get_option( 'mc_nuke_all_styles' ) !== '1' ) {
		wp_enqueue_style( 'mailchimp_sf_main_css', home_url( '?mcsf_action=main_css&ver=' . MCSF_VER, 'relative' ), array(), MCSF_VER );
		wp_enqueue_style( 'mailchimp_sf_ie_css', MCSF_URL . 'css/ie.css', array(), MCSF_VER );
		global $wp_styles;
		$wp_styles->add_data( 'mailchimp_sf_ie_css', 'conditional', 'IE' );
	}
}


/**
 * Loads resources for the Mailchimp admin page
 *
 * @return void
 */
function mc_admin_page_load_resources() {
	wp_enqueue_style( 'mailchimp_sf_admin_css', MCSF_URL . 'css/admin.css', array(), true );
}

add_action( 'load-settings_page_mailchimp_sf_options', 'mc_admin_page_load_resources' );


/**
 * Loads jQuery Datepicker for the date-pick class
 **/
function mc_datepicker_load() {
	require_once MCSF_DIR . '/views/datepicker.php';
}

if ( get_option( 'mc_use_datepicker' ) === 'on' && ! is_admin() ) {
	add_action( 'wp_head', 'mc_datepicker_load' );
}

/**
 * Handles requests that as light-weight a load as possible.
 * typically, JS or CSS
 *
 * @return void
 */
function mailchimp_sf_early_request_handler() {
	if ( isset( $_GET['mcsf_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- ignoring because this is only adding CSS
		switch ( $_GET['mcsf_action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- ignoring because this is only adding CSS
			case 'main_css':
				header( 'Content-type: text/css' );
				mailchimp_sf_main_css();
				exit;
		}
	}
}

add_action( 'init', 'mailchimp_sf_early_request_handler', 0 );

/**
 * Outputs the front-end CSS.  This checks several options, so it
 * was best to put it in a Request-handled script, as opposed to
 * a static file.
 */
function mailchimp_sf_main_css() {
	require_once MCSF_DIR . '/views/css/frontend.php';
}


/**
 * Add our settings page to the admin menu
 *
 * @return void
 */
function mailchimp_sf_add_pages() {
	// Add settings page for users who can edit plugins
	add_options_page(
		__( 'Mailchimp Setup', 'mailchimp_i18n' ),
		__( 'Mailchimp Setup', 'mailchimp_i18n' ),
		MCSF_CAP_THRESHOLD,
		'mailchimp_sf_options',
		'mailchimp_sf_setup_page'
	);
}
add_action( 'admin_menu', 'mailchimp_sf_add_pages' );

/**
 * Request handler
 *
 * @return void
 */
function mailchimp_sf_request_handler() {
	if ( isset( $_POST['mcsf_action'] ) ) {
		switch ( $_POST['mcsf_action'] ) {
			case 'login':
				$key = isset( $_POST['mailchimp_sf_api_key'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['mailchimp_sf_api_key'] ) ) ) : '';

				try {
					$api = new MailChimp_API( $key );
				} catch ( Exception $e ) {
					$msg = '<strong class="mc_error_msg">' . $e->getMessage() . '</strong>';
					mailchimp_sf_global_msg( $msg );
					break;
				}

				$key = mailchimp_sf_verify_key( $api );
				if ( is_wp_error( $key ) ) {
					$msg = '<strong class="mc_error_msg">' . $key->get_error_message() . '</strong>';
					mailchimp_sf_global_msg( $msg );
				}

				break;
			case 'logout':
				// Check capability & Verify nonce
				if (
					! current_user_can( MCSF_CAP_THRESHOLD ) ||
					! isset( $_POST['_mcsf_nonce_action'] ) ||
					! wp_verify_nonce( sanitize_key( $_POST['_mcsf_nonce_action'] ), 'mc_logout' )
				) {
					wp_die( 'Cheatin&rsquo; huh?' );
				}

				// erase auth information
				$options = array( 'mc_api_key', 'mc_sopresto_user', 'mc_sopresto_public_key', 'mc_sopresto_secret_key' );
				mailchimp_sf_delete_options( $options );
				break;
			case 'change_form_settings':
				if (
					! current_user_can( MCSF_CAP_THRESHOLD ) ||
					! isset( $_POST['_mcsf_nonce_action'] ) ||
					! wp_verify_nonce( sanitize_key( $_POST['_mcsf_nonce_action'] ), 'update_general_form_settings' )
				) {
					wp_die( 'Cheatin&rsquo; huh?' );
				}

				// Update the form settings
				mailchimp_sf_save_general_form_settings();
				break;
			case 'mc_submit_signup_form':
				// Validate nonce
				if (
					! isset( $_POST['_mc_submit_signup_form_nonce'] ) ||
					! wp_verify_nonce( sanitize_key( $_POST['_mc_submit_signup_form_nonce'] ), 'mc_submit_signup_form' )
				) {
					wp_die( 'Cheatin&rsquo; huh?' );
				}

				// Attempt the signup
				mailchimp_sf_signup_submit();

				// Do a different action for html vs. js
				switch ( isset( $_POST['mc_submit_type'] ) ? $_POST['mc_submit_type'] : '' ) {
					case 'html':
						/* This gets set elsewhere! */
						break;
					case 'js':
						if ( ! headers_sent() ) { // just in case...
							header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT', true, 200 );
						}
						echo wp_kses_post( mailchimp_sf_global_msg() );
						exit;
				}
		}
	}
}
add_action( 'init', 'mailchimp_sf_request_handler' );

/**
 * Migrate Sopresto
 *
 * @return void
 */
function mailchimp_sf_migrate_sopresto() {
	$sopresto = get_option( 'mc_sopresto_secret_key' );
	if ( ! $sopresto ) {
		return;
	}

	// Talk to Sopresto, make exchange, delete old sopresto things.
	$body = array(
		'public_key' => get_option( 'mc_sopresto_public_key' ),
		'hash'       => sha1( get_option( 'mc_sopresto_public_key' ) . get_option( 'mc_sopresto_secret_key' ) ),
	);

	$url  = 'https://sopresto.socialize-this.com/mailchimp/exchange';
	$args = array(
		'method'      => 'POST',
		'timeout'     => 500,
		'redirection' => 5,
		'httpversion' => '1.0',
		'user-agent'  => 'Mailchimp WordPress Plugin/' . get_bloginfo( 'url' ),
		'body'        => $body,
	);

	// post to sopresto
	$key = wp_remote_post( $url, $args );
	if ( ! is_wp_error( $key ) && 200 === $key['response']['code'] ) {
		$key = json_decode( $key['body'] );
		try {
			$api = new MailChimp_API( $key->response );
		} catch ( Exception $e ) {
			$msg = '<strong class="mc_error_msg">' . $e->getMessage() . '</strong>';
			mailchimp_sf_global_msg( $msg );
			return;
		}

		$verify = mailchimp_sf_verify_key( $api );

		// something went wrong with the key that we had
		if ( is_wp_error( $verify ) ) {
			return;
		}

		delete_option( 'mc_sopresto_public_key' );
		delete_option( 'mc_sopresto_secret_key' );
		delete_option( 'mc_sopresto_user' );
	}
}

/**
 * Update merge fields
 *
 * @return void
 */
function mailchimp_sf_update_merge_fields() {
	mailchimp_sf_get_merge_vars( get_option( 'mc_list_id' ), true );
	mailchimp_sf_get_interest_categories( get_option( 'mc_list_id' ), true );
	update_option( 'mc_merge_field_migrate', true );
}

/**
 * Get auth key
 *
 * @param mixed $salt Salt
 * @return string
 */
function mailchimp_sf_auth_nonce_key( $salt = null ) {
	if ( is_null( $salt ) ) {
		$salt = mailchimp_sf_auth_nonce_salt();
	}
	return 'social_authentication' . md5( AUTH_KEY . $salt );
}

/**
 * Return auth nonce salt
 *
 * @return string
 */
function mailchimp_sf_auth_nonce_salt() {
	return md5( microtime() . isset( $_SERVER['SERVER_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) : '' );
}

/**
 * Creates new Mailchimp API v3 object
 *
 * @return MailChimp_API | false
 */
function mailchimp_sf_get_api() {
	$key = get_option( 'mc_api_key' );
	if ( $key ) {
		return new MailChimp_API( $key );
	}

	return false;
}

/**
 * Checks to see if we're storing a password, if so, we need
 * to upgrade to the API key
 *
 * @return bool
 **/
function mailchimp_sf_needs_upgrade() {
	$igs = get_option( 'mc_interest_groups' );

	if ( false !== $igs // we have an option
		&& (
			empty( $igs ) || // it can be an empty array (no interest groups)
			( is_array( $igs ) && isset( $igs[0]['id'] ) ) // OR it should be a populated array that's well-formed
		)
	) {
		return false; // no need to upgrade
	} else {
		return true; // yeah, let's do it
	}
}

/**
 * Deletes all Mailchimp options
 **/
function mailchimp_sf_delete_setup() {
	$options = array(
		'mc_user_id',
		'mc_sopresto_user',
		'mc_sopresto_public_key',
		'mc_sopresto_secret_key',
		'mc_rewards',
		'mc_use_javascript',
		'mc_use_datepicker',
		'mc_use_unsub_link',
		'mc_list_id',
		'mc_list_name',
		'mc_interest_groups',
		'mc_merge_vars',
	);

	$igs = get_option( 'mc_interest_groups' );
	if ( is_array( $igs ) ) {
		foreach ( $igs as $ig ) {
			$opt       = 'mc_show_interest_groups_' . $ig['id'];
			$options[] = $opt;
		}
	}

	$mv = get_option( 'mc_merge_vars' );
	if ( is_array( $mv ) ) {
		foreach ( $mv as $mv_var ) {
			$opt       = 'mc_mv_' . $mv_var['tag'];
			$options[] = $opt;
		}
	}

	mailchimp_sf_delete_options( $options );
}

/**
 * Gets or sets a global message based on parameter passed to it
 *
 * @param mixed $msg Message
 * @return string/bool depending on get/set
 */
function mailchimp_sf_global_msg( $msg = null ) {
	global $mcsf_msgs;

	// Make sure we're formed properly
	if ( ! is_array( $mcsf_msgs ) ) {
		$mcsf_msgs = array();
	}

	// See if we're getting
	if ( is_null( $msg ) ) {
		return implode( '', $mcsf_msgs );
	}

	// Must be setting
	$mcsf_msgs[] = $msg;
	return true;
}

/**
 * Sets the default options for the option form
 *
 * @param string $list_name The Mailchimp list name.
 * @return void
 */
function mailchimp_sf_set_form_defaults( $list_name = '' ) {
	update_option( 'mc_header_content', __( 'Sign up for', 'mailchimp_i18n' ) . ' ' . $list_name );
	update_option( 'mc_submit_text', __( 'Subscribe', 'mailchimp_i18n' ) );

	update_option( 'mc_use_datepicker', 'on' );
	update_option( 'mc_custom_style', 'off' );
	update_option( 'mc_use_javascript', 'on' );
	update_option( 'mc_double_optin', true );
	update_option( 'mc_use_unsub_link', 'off' );
	update_option( 'mc_header_border_width', '1' );
	update_option( 'mc_header_border_color', 'E3E3E3' );
	update_option( 'mc_header_background', 'FFFFFF' );
	update_option( 'mc_header_text_color', 'CC6600' );

	update_option( 'mc_form_border_width', '1' );
	update_option( 'mc_form_border_color', 'E0E0E0' );
	update_option( 'mc_form_background', 'FFFFFF' );
	update_option( 'mc_form_text_color', '3F3F3f' );
}

/**
 * Saves the General Form settings on the options page
 *
 * @return void
 **/
function mailchimp_sf_save_general_form_settings() {

	// IF NOT DEV MODE
	if ( isset( $_POST['mc_rewards'] ) ) {
		update_option( 'mc_rewards', 'on' );
		$msg = '<p class="success_msg">' . __( 'Monkey Rewards turned On!', 'mailchimp_i18n' ) . '</p>';
		mailchimp_sf_global_msg( $msg );
	} elseif ( get_option( 'mc_rewards' ) !== 'off' ) {
		update_option( 'mc_rewards', 'off' );
		$msg = '<p class="success_msg">' . __( 'Monkey Rewards turned Off!', 'mailchimp_i18n' ) . '</p>';
		mailchimp_sf_global_msg( $msg );
	}
	if ( isset( $_POST['mc_use_javascript'] ) ) {
		update_option( 'mc_use_javascript', 'on' );
		$msg = '<p class="success_msg">' . __( 'Fancy Javascript submission turned On!', 'mailchimp_i18n' ) . '</p>';
		mailchimp_sf_global_msg( $msg );
	} elseif ( get_option( 'mc_use_javascript' ) !== 'off' ) {
		update_option( 'mc_use_javascript', 'off' );
		$msg = '<p class="success_msg">' . __( 'Fancy Javascript submission turned Off!', 'mailchimp_i18n' ) . '</p>';
		mailchimp_sf_global_msg( $msg );
	}

	if ( isset( $_POST['mc_use_datepicker'] ) ) {
		update_option( 'mc_use_datepicker', 'on' );
		$msg = '<p class="success_msg">' . __( 'Datepicker turned On!', 'mailchimp_i18n' ) . '</p>';
		mailchimp_sf_global_msg( $msg );
	} elseif ( get_option( 'mc_use_datepicker' ) !== 'off' ) {
		update_option( 'mc_use_datepicker', 'off' );
		$msg = '<p class="success_msg">' . __( 'Datepicker turned Off!', 'mailchimp_i18n' ) . '</p>';
		mailchimp_sf_global_msg( $msg );
	}

	/*Enable double optin toggle*/
	if ( isset( $_POST['mc_double_optin'] ) ) {
		update_option( 'mc_double_optin', true );
		$msg = '<p class="success_msg">' . __( 'Double opt-in turned On!', 'mailchimp_i18n' ) . '</p>';
		mailchimp_sf_global_msg( $msg );
	} elseif ( get_option( 'mc_double_optin' ) !== false ) {
		update_option( 'mc_double_optin', false );
		$msg = '<p class="success_msg">' . __( 'Double opt-in turned Off!', 'mailchimp_i18n' ) . '</p>';
		mailchimp_sf_global_msg( $msg );
	}

	/* NUKE the CSS! */
	if ( isset( $_POST['mc_nuke_all_styles'] ) ) {
		update_option( 'mc_nuke_all_styles', true );
		$msg = '<p class="success_msg">' . __( 'Mailchimp CSS turned Off!', 'mailchimp_i18n' ) . '</p>';
		mailchimp_sf_global_msg( $msg );
	} elseif ( get_option( 'mc_nuke_all_styles' ) !== false ) {
		update_option( 'mc_nuke_all_styles', false );
		$msg = '<p class="success_msg">' . __( 'Mailchimp CSS turned On!', 'mailchimp_i18n' ) . '</p>';
		mailchimp_sf_global_msg( $msg );
	}

	/* Update existing */
	if ( isset( $_POST['mc_update_existing'] ) ) {
		update_option( 'mc_update_existing', true );
		$msg = '<p class="success_msg">' . __( 'Update existing subscribers turned On!' ) . '</p>';
		mailchimp_sf_global_msg( $msg );
	} elseif ( get_option( 'mc_update_existing' ) !== false ) {
		update_option( 'mc_update_existing', false );
		$msg = '<p class="success_msg">' . __( 'Update existing subscribers turned Off!' ) . '</p>';
		mailchimp_sf_global_msg( $msg );
	}

	if ( isset( $_POST['mc_use_unsub_link'] ) ) {
		update_option( 'mc_use_unsub_link', 'on' );
		$msg = '<p class="success_msg">' . __( 'Unsubscribe link turned On!', 'mailchimp_i18n' ) . '</p>';
		mailchimp_sf_global_msg( $msg );
	} elseif ( get_option( 'mc_use_unsub_link' ) !== 'off' ) {
		update_option( 'mc_use_unsub_link', 'off' );
		$msg = '<p class="success_msg">' . __( 'Unsubscribe link turned Off!', 'mailchimp_i18n' ) . '</p>';
		mailchimp_sf_global_msg( $msg );
	}

	$content = isset( $_POST['mc_header_content'] ) ? wp_kses_post( wp_unslash( $_POST['mc_header_content'] ) ) : '';
	$content = str_replace( "\r\n", '<br/>', $content );
	update_option( 'mc_header_content', $content );

	$content = isset( $_POST['mc_subheader_content'] ) ? wp_kses_post( wp_unslash( $_POST['mc_subheader_content'] ) ) : '';
	$content = str_replace( "\r\n", '<br/>', $content );
	update_option( 'mc_subheader_content', $content );

	$submit_text = isset( $_POST['mc_submit_text'] ) ? sanitize_text_field( wp_unslash( $_POST['mc_submit_text'] ) ) : '';
	$submit_text = str_replace( "\r\n", '', $submit_text );
	update_option( 'mc_submit_text', $submit_text );

	// Set Custom Style option
	update_option( 'mc_custom_style', isset( $_POST['mc_custom_style'] ) ? 'on' : 'off' );

	// we told them not to put these things we are replacing in, but let's just make sure they are listening...
	if ( isset( $_POST['mc_form_border_width'] ) ) {
		update_option( 'mc_form_border_width', str_replace( 'px', '', absint( $_POST['mc_form_border_width'] ) ) );
	}
	if ( isset( $_POST['mc_form_border_color'] ) ) {
		update_option( 'mc_form_border_color', str_replace( '#', '', sanitize_text_field( wp_unslash( $_POST['mc_form_border_color'] ) ) ) );
	}
	if ( isset( $_POST['mc_form_background'] ) ) {
		update_option( 'mc_form_background', str_replace( '#', '', sanitize_text_field( wp_unslash( $_POST['mc_form_background'] ) ) ) );
	}
	if ( isset( $_POST['mc_form_text_color'] ) ) {
		update_option( 'mc_form_text_color', str_replace( '#', '', sanitize_text_field( wp_unslash( $_POST['mc_form_text_color'] ) ) ) );
	}

	// IF NOT DEV MODE
	$igs = get_option( 'mc_interest_groups' );
	if ( is_array( $igs ) ) {
		foreach ( $igs as $mv_var ) {
			$opt = 'mc_show_interest_groups_' . $mv_var['id'];
			if ( isset( $_POST[ $opt ] ) ) {
				update_option( $opt, 'on' );
			} else {
				update_option( $opt, 'off' );
			}
		}
	}

	$mv = get_option( 'mc_merge_vars' );
	if ( is_array( $mv ) ) {
		foreach ( $mv as $mv_var ) {
			$opt = 'mc_mv_' . $mv_var['tag'];
			if ( isset( $_POST[ $opt ] ) || 'Y' === $mv_var['required'] ) {
				update_option( $opt, 'on' );
			} else {
				update_option( $opt, 'off' );
			}
		}
	}

	$msg = '<p class="success_msg">' . esc_html( __( 'Successfully Updated your List Subscribe Form Settings!', 'mailchimp_i18n' ) ) . '</p>';
	mailchimp_sf_global_msg( $msg );
}

/**
 * Sees if the user changed the list, and updates options accordingly
 **/
function mailchimp_sf_change_list_if_necessary() {
	// Simple permission check before going through all this
	if ( ! current_user_can( MCSF_CAP_THRESHOLD ) ) { return; }

	$api = mailchimp_sf_get_api();
	if ( ! $api ) { return; }

	// we *could* support paging, but few users have that many lists (and shouldn't)
	$lists = $api->get( 'lists', 100, array( 'fields' => 'lists.id,lists.name,lists.email_type_option' ) );

	if ( ! isset( $lists['lists'] ) || is_wp_error( $lists['lists'] ) ) {
		return;
	}

	$lists = $lists['lists'];

	if ( is_array( $lists ) && ! empty( $lists ) && isset( $_POST['mc_list_id'] ) ) {

		/**
		 * If our incoming list ID (the one chosen in the select dropdown)
		 * is in our array of lists, the set it to be the active list
		 */
		foreach ( $lists as $key => $list ) {
			if ( isset( $_POST['mc_list_id'] ) && $list['id'] === $_POST['mc_list_id'] ) {
				$list_id   = sanitize_text_field( wp_unslash( $_POST['mc_list_id'] ) );
				$list_name = $list['name'];
				$list_key  = $key;
			}
		}

		$orig_list = get_option( 'mc_list_id' );
		if ( '' !== $list_id ) {
			update_option( 'mc_list_id', $list_id );
			update_option( 'mc_list_name', $list_name );
			update_option( 'mc_email_type_option', $lists[ $list_key ]['email_type_option'] );

			// See if the user changed the list
			$new_list = false;
			if ( $orig_list !== $list_id ) {
				// The user changed the list, Reset the Form Defaults
				mailchimp_sf_set_form_defaults( $list_name );

				$new_list = true;
			}

			// Grab the merge vars and interest groups
			$mv  = mailchimp_sf_get_merge_vars( $list_id, $new_list );
			$igs = mailchimp_sf_get_interest_categories( $list_id, $new_list );

			$igs_text = ' ';
			if ( is_array( $igs ) ) {
				// translators: placeholder is a count (number)
				$igs_text .= sprintf( __( 'and %s Sets of Interest Groups', 'mailchimp_i18n' ), count( $igs ) );
			}

			$msg = '<p class="success_msg">' .
				sprintf(
					// translators: placeholder is a count (number)
					__( '<b>Success!</b> Loaded and saved the info for %d Merge Variables', 'mailchimp_i18n' ) . $igs_text,
					count( $mv )
				) . ' ' .
				__( 'from your list' ) . ' "' . $list_name . '"<br/><br/>' .
				__( 'Now you should either Turn On the Mailchimp Widget or change your options below, then turn it on.', 'mailchimp_i18n' ) . '</p>';
			mailchimp_sf_global_msg( $msg );
		}
	}
}

/**
 * Get merge vars
 *
 * @param string $list_id List ID
 * @param bool   $new_list Whether this is a new list
 * @return array
 */
function mailchimp_sf_get_merge_vars( $list_id, $new_list ) {
	$api = mailchimp_sf_get_api();
	$mv  = $api->get( 'lists/' . $list_id . '/merge-fields', 80 );

	// if we get an error back from the api, exit this process.
	if ( is_wp_error( $mv ) ) {
		return;
	}

	$mv['merge_fields'] = mailchimp_sf_add_email_field( $mv['merge_fields'] );
	update_option( 'mc_merge_vars', $mv['merge_fields'] );
	foreach ( $mv['merge_fields'] as $mv_var ) {
		$opt = 'mc_mv_' . $mv_var['tag'];
		// turn them all on by default
		if ( $new_list ) {
			update_option( $opt, 'on' );
		}
	}
	return $mv['merge_fields'];
}

/**
 * Add email field
 *
 * @param array $merge Merge
 * @return array
 */
function mailchimp_sf_add_email_field( $merge ) {
	$email = array(
		'tag'           => 'EMAIL',
		'name'          => __( 'Email Address', 'mailchimp_i18n' ),
		'type'          => 'email',
		'required'      => true,
		'public'        => true,
		'display_order' => 1,
		'default_value' => null,
	);
	array_unshift( $merge, $email );
	return $merge;
}

/**
 * Get interest categories
 *
 * @param string $list_id List ID
 * @param bool   $new_list Whether this is a new list
 * @return array
 */
function mailchimp_sf_get_interest_categories( $list_id, $new_list ) {
	$api = mailchimp_sf_get_api();
	$igs = $api->get( 'lists/' . $list_id . '/interest-categories', 60 );

	// if we get an error back from the api, exis
	if ( is_wp_error( $igs ) ) {
		return;
	}

	if ( is_array( $igs ) ) {
		$key = 0;
		foreach ( $igs['categories'] as $ig ) {
			$groups                              = $api->get( 'lists/' . $list_id . '/interest-categories/' . $ig['id'] . '/interests', 60 );
			$igs['categories'][ $key ]['groups'] = $groups['interests'];
			$opt                                 = 'mc_show_interest_groups_' . $ig['id'];

			// turn them all on by default
			if ( $new_list ) {
				update_option( $opt, 'on' );
			}
			++$key;
		}
	}
	update_option( 'mc_interest_groups', $igs['categories'] );
	return $igs['categories'];
}


/**
 * Outputs the Settings/Options page
 */
function mailchimp_sf_setup_page() {
	$path = plugin_dir_path( __FILE__ );
	wp_enqueue_script( 'showMe', MCSF_URL . 'js/hidecss.js', array( 'jquery' ), MCSF_VER, true );
	require_once $path . '/views/setup_page.php';
}

/**
 * Register the widget.
 *
 * @return void
 */
function mailchimp_sf_register_widgets() {
	if ( mailchimp_sf_get_api() ) {
		register_widget( 'Mailchimp_SF_Widget' );
	}
}
add_action( 'widgets_init', 'mailchimp_sf_register_widgets' );

/**
 * Add shortcode
 *
 * @return string
 */
function mailchimp_sf_shortcode() {
	ob_start();
	mailchimp_sf_signup_form();
	return ob_get_clean();
}
add_shortcode( 'mailchimpsf_form', 'mailchimp_sf_shortcode' );

/**
 * Attempts to signup a user, per the $_POST args.
 *
 * This sets a global message, that is then used in the widget
 * output to retrieve and display that message.
 *
 * @return bool
 */
function mailchimp_sf_signup_submit() {
	$mv          = get_option( 'mc_merge_vars', array() );
	$mv_tag_keys = array();

	$igs = get_option( 'mc_interest_groups', array() );

	$list_id = get_option( 'mc_list_id' );
	$email   = isset( $_POST['mc_mv_EMAIL'] ) ? wp_strip_all_tags( wp_unslash( $_POST['mc_mv_EMAIL'] ) ) : '';

	$merge = mailchimp_sf_merge_submit( $mv );

	// Catch errors and fail early.
	if ( is_wp_error( $merge ) ) {
		$msg = '<strong class="mc_error_msg">' . $merge->get_error_message() . '</strong>';
		mailchimp_sf_global_msg( $msg );

		return false;
	}

	// Head back to the beginning of the merge vars array
	reset( $mv );
	// Ensure we have an array
	$igs = ! is_array( $igs ) ? array() : $igs;
	$igs = mailchimp_sf_groups_submit( $igs );

	// Clear out empty merge vars
	$merge = mailchimp_sf_merge_remove_empty( $merge );
	if ( isset( $_POST['email_type'] ) && in_array( $_POST['email_type'], array( 'text', 'html', 'mobile' ), true ) ) {
		$email_type = sanitize_text_field( wp_unslash( $_POST['email_type'] ) );
	} else {
		$email_type = 'html';
	}

	$api = mailchimp_sf_get_api();
	if ( ! $api ) {
		$url   = mailchimp_sf_signup_form_url();
		$error = sprintf(
			'<strong class="mc_error_msg">%s</strong>',
			wp_kses(
				sprintf(
					// translators: first placeholder is email address, second is url
					__(
						'We encountered a problem adding %1$s to the list. Please <a href="%2$s">sign up here.</a>',
						'mailchimp_i18n'
					),
					esc_html( $email ),
					esc_url( $url )
				),
				[
					'a' => [
						'href',
					],
				]
			)
		);
		mailchimp_sf_global_msg( $error );
		return false;
	}

	$url    = 'lists/' . $list_id . '/members/' . md5( strtolower( $email ) );
	$status = mailchimp_sf_check_status( $url );

	// If update existing is turned off and the subscriber exists, error out.
	if ( get_option( 'mc_update_existing' ) === false && 'subscribed' === $status ) {
		$msg   = 'This email address is already subscribed to the list.';
		$error = new WP_Error( 'mailchimp-update-existing', $msg );
		mailchimp_sf_global_msg( '<strong class="mc_error_msg">' . $msg . '</strong>' );
		return false;
	}

	$body   = mailchimp_sf_subscribe_body( $merge, $igs, $email_type, $email, $status, get_option( 'mc_double_optin' ) );
	$retval = $api->post( $url, $body, 'PUT' );

	// If we have errors, then show them
	if ( is_wp_error( $retval ) ) {
		$msg = '<strong class="mc_error_msg">' . $retval->get_error_message() . '</strong>';
		mailchimp_sf_global_msg( $msg );
		return false;
	}

	if ( 'subscribed' === $retval['status'] ) {
		$esc = __( 'Success, you\'ve been signed up.', 'mailchimp_i18n' );
		$msg = "<strong class='mc_success_msg'>{$esc}</strong>";
	} else {
		$esc = __( 'Success, you\'ve been signed up! Please look for our confirmation email.', 'mailchimp_i18n' );
		$msg = "<strong class='mc_success_msg'>{$esc}</strong>";
	}

	// Set our global message
	mailchimp_sf_global_msg( $msg );

	return true;
}

/**
 * Cleans up merge fields and interests to make them
 * API 3.0-friendly.
 *
 * @param [type] $merge Merge fields
 * @param [type] $igs Interest groups
 * @param string $email_type Email type
 * @param string $email Email
 * @param string $status Status
 * @param bool   $double_optin Whether this is double optin
 * @return stdClass
 */
function mailchimp_sf_subscribe_body( $merge, $igs, $email_type, $email, $status, $double_optin ) {
	$body                = new stdClass();
	$body->email_address = $email;
	$body->email_type    = $email_type;
	$body->merge_fields  = $merge;
	if ( ! empty( $igs ) ) {
		$body->interests = $igs;
	}

	if ( 'subscribed' !== $status ) {
		// single opt-in that covers new subscribers
		if ( false === ! $status && $double_optin ) {
			$body->status = 'subscribed';
		} else {
			// anyone else
			$body->status = 'pending';
		}
	}
	return $body;
}

/**
 * Check status.
 *
 * @param string $endpoint Endpoint.
 * @return string
 */
function mailchimp_sf_check_status( $endpoint ) {
	$endpoint  .= '?fields=status';
	$api        = mailchimp_sf_get_api();
	$subscriber = $api->get( $endpoint, null );
	if ( is_wp_error( $subscriber ) ) {
		return false;
	}
	return $subscriber['status'];
}

/**
 * Merge submit
 *
 * @param array $mv Merge Vars
 * @return mixed
 */
function mailchimp_sf_merge_submit( $mv ) {
	// Loop through our Merge Vars, and if they're empty, but required, then print an error, and mark as failed
	$merge = new stdClass();
	foreach ( $mv as $mv_var ) {
		// We also want to create an array where the keys are the tags for easier validation later
		$tag                 = $mv_var['tag'];
		$mv_tag_keys[ $tag ] = $mv_var;

		$opt = 'mc_mv_' . $tag;

		$opt_val = isset( $_POST[ $opt ] ) ? map_deep( stripslashes_deep( $_POST[ $opt ] ), 'sanitize_text_field' ) : '';

		// Handle phone number logic
		if ( isset( $mv_var['options']['phone_format'] ) && 'phone' === $mv_var['type'] && 'US' === $mv_var['options']['phone_format'] ) {
			$opt_val = mailchimp_sf_merge_validate_phone( $opt_val, $mv_var );
			if ( is_wp_error( $opt_val ) ) {
				return $opt_val;
			}
		} elseif ( is_array( $opt_val ) && 'address' === $mv_var['type'] ) { // Handle address logic
			$validate = mailchimp_sf_merge_validate_address( $opt_val, $mv_var );
			if ( is_wp_error( $validate ) ) {
				return $validate;
			}

			if ( $validate ) {
				$merge->$tag = $validate;
			}
			continue;

		} elseif ( is_array( $opt_val ) ) {
			$keys = array_keys( $opt_val );
			$val  = new stdClass();
			foreach ( $keys as $key ) {
				$val->$key = $opt_val[ $key ];
			}
			$opt_val = $val;
		}

		if ( 'Y' === $mv_var['required'] && trim( $opt_val ) === '' ) {
			// translators: placeholder is field name
			$message = sprintf( __( 'You must fill in %s.', 'mailchimp_i18n' ), esc_html( $mv_var['name'] ) );
			$error   = new WP_Error( 'missing_required_field', $message );
			return $error;
		} elseif ( 'EMAIL' !== $tag ) {
			$merge->$tag = $opt_val;
		}
	}
	return $merge;
}

/**
 * Validate phone
 *
 * @param array $opt_val Option value
 * @param array $data Data
 * @return void
 */
function mailchimp_sf_merge_validate_phone( $opt_val, $data ) {
	// This filters out all 'falsey' elements
	$opt_val = array_filter( $opt_val );
	// If they weren't all empty
	if ( ! $opt_val ) {
		return;
	}

	$opt_val = implode( '-', $opt_val );
	if ( strlen( $opt_val ) < 12 ) {
		$opt_val = '';
	}

	if ( ! preg_match( '/[0-9]{0,3}-[0-9]{0,3}-[0-9]{0,4}/A', $opt_val ) ) {
		// translators: placeholder is field name
		$message = sprintf( __( '%s must consist of only numbers', 'mailchimp_i18n' ), esc_html( $data['name'] ) );
		$error   = new WP_Error( 'mc_phone_validation', $message );
		return $error;
	}

	return $opt_val;
}

/**
 * Validate address
 *
 * @param array $opt_val Option value
 * @param array $data Data
 * @return mixed
 */
function mailchimp_sf_merge_validate_address( $opt_val, $data ) {
	if ( 'Y' === $data['required'] ) {
		if ( empty( $opt_val['addr1'] ) || empty( $opt_val['city'] ) ) {
			// translators: placeholder is field name
			$message = sprintf( __( 'You must fill in %s.', 'mailchimp_i18n' ), esc_html( $data['name'] ) );
			$error   = new WP_Error( 'invalid_address_merge', $message );
			return $error;
		}
	} elseif ( empty( $opt_val['addr1'] ) || empty( $opt_val['city'] ) ) {
		return false;
	}

	$merge          = new stdClass();
	$merge->addr1   = $opt_val['addr1'];
	$merge->addr2   = $opt_val['addr2'];
	$merge->city    = $opt_val['city'];
	$merge->state   = $opt_val['state'];
	$merge->zip     = $opt_val['zip'];
	$merge->country = $opt_val['country'];
	return $merge;
}

/**
 * Merge remove empty
 *
 * @param stdObj $merge Merge
 * @return stdObj
 */
function mailchimp_sf_merge_remove_empty( $merge ) {
	foreach ( $merge as $k => $v ) {
		if ( is_object( $v ) && empty( $v ) ) {
			unset( $merge->$k );
		} elseif ( ( is_string( $v ) && trim( $v ) === '' ) || is_null( $v ) ) {
			unset( $merge->$k );
		}
	}

	return $merge;
}

/**
 * Groups submit
 *
 * @param array $igs Interest groups
 * @return stdClass
 */
function mailchimp_sf_groups_submit( $igs ) {
	$groups = mailchimp_sf_set_all_groups_to_false();

	if ( empty( $igs ) ) {
		return new StdClass();
	}

	// get groups and ids
	// set all to false

	foreach ( $igs as $ig ) {
		$ig_id = $ig['id'];
		if ( get_option( 'mc_show_interest_groups_' . $ig_id ) === 'on' && 'hidden' !== $ig['type'] ) {
			switch ( $ig['type'] ) {
				case 'dropdown':
				case 'radio':
					// there can only be one value submitted for radio/dropdowns, so use that at the group id.
					if ( isset( $_POST['group'][ $ig_id ] ) && ! empty( $_POST['group'][ $ig_id ] ) ) {
						$value          = sanitize_text_field( wp_unslash( $_POST['group'][ $ig_id ] ) );
						$groups->$value = true;
					}
					break;
				case 'checkboxes':
					if ( isset( $_POST['group'][ $ig_id ] ) ) {
						$ig_ids = array_map(
							'sanitize_text_field',
							array_keys(
								stripslashes_deep( $_POST['group'][ $ig_id ] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- ignoring becuase this is sanitized through array_map above
							)
						);
						foreach ( $ig_ids as $id ) {
							$groups->$id = true;
						}
					}
					break;
				default:
					// Nothing
					break;
			}
		}
	}
	return $groups;
}

/**
 * Set all groups to false
 *
 * @return StdClass
 */
function mailchimp_sf_set_all_groups_to_false() {
	$toreturn = new StdClass();

	foreach ( get_option( 'mc_interest_groups' ) as $grouping ) {
		if ( 'hidden' !== $grouping['type'] ) {
			foreach ( $grouping['groups'] as $group ) {
				$id            = $group['id'];
				$toreturn->$id = false;
			}
		}
	}

	return $toreturn;
}

/**
 * Verify key
 *
 * @param MailChimp_API $api API instance
 * @return mixed
 */
function mailchimp_sf_verify_key( $api ) {
	$user = $api->get( '' );
	if ( is_wp_error( $user ) ) {
		return $user;
	}

	// Might as well set this data if we have it already.
	$valid_roles = array( 'owner', 'admin', 'manager' );
	if ( in_array( $user['role'], $valid_roles, true ) ) {
		update_option( 'mc_api_key', $api->key );
		update_option( 'mc_user', $user );
		update_option( 'mc_datacenter', $api->datacenter );

	} else {
		$msg = __( 'API Key must belong to "Owner", "Admin", or "Manager."', 'mailchimp_i18n' );
		return new WP_Error( 'mc-invalid-role', $msg );
	}
}

/**
 * Update profile URL.
 *
 * @param string $email Email
 * @return string
 */
function mailchimp_sf_update_profile_url( $email ) {
	$dc = get_option( 'mc_datacenter' );
	// This is the expected encoding for emails.
	$eid     = base64_encode( $email ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- ignoring because this is the expected data for the endpoint
	$user    = get_option( 'mc_user' );
	$list_id = get_option( 'mc_list_id' );
	$url     = 'http://' . $dc . '.list-manage.com/subscribe/send-email?u=' . $user['account_id'] . '&id=' . $list_id . '&e=' . $eid;
	return $url;
}

/**
 * Get signup form URL.
 *
 * @return string
 */
function mailchimp_sf_signup_form_url() {
	$dc      = get_option( 'mc_datacenter' );
	$user    = get_option( 'mc_user' );
	$list_id = get_option( 'mc_list_id' );
	$url     = 'http://' . $dc . '.list-manage.com/subscribe?u=' . $user['account_id'] . '&id=' . $list_id;
	return $url;
}

/**
 * Delete options
 *
 * @param array $options Options
 * @return void
 */
function mailchimp_sf_delete_options( $options = array() ) {
	foreach ( $options as $option ) {
		delete_option( $option );
	}
}

/**********************
 * Utility Functions *
 **********************/
/**
 * Utility function to allow placement of plugin in plugins, mu-plugins, child or parent theme's plugins folders
 *
 * This function must be ran _very early_ in the load process, as it sets up important constants for the rest of the plugin
 */
function mailchimp_sf_where_am_i() {
	$locations = array(
		'plugins'    => array(
			'dir' => plugin_dir_path( __FILE__ ),
			'url' => plugins_url(),
		),
		'mu_plugins' => array(
			'dir' => plugin_dir_path( __FILE__ ),
			'url' => plugins_url(),
		),
		'template'   => array(
			'dir' => trailingslashit( get_template_directory() ) . 'plugins/',
			'url' => trailingslashit( get_template_directory_uri() ) . 'plugins/',
		),
		'stylesheet' => array(
			'dir' => trailingslashit( get_stylesheet_directory() ) . 'plugins/',
			'url' => trailingslashit( get_stylesheet_directory_uri() ) . 'plugins/',
		),
	);

	// Set defaults
	$mscf_dirbase = trailingslashit( basename( __DIR__ ) );  // Typically wp-mailchimp/ or mailchimp/
	$mscf_dir     = trailingslashit( plugin_dir_path( __FILE__ ) );
	$mscf_url     = trailingslashit( plugins_url( '', __FILE__ ) );

	// Try our hands at finding the real location
	foreach ( $locations as $key => $loc ) {
		$dir = trailingslashit( $loc['dir'] ) . $mscf_dirbase;
		$url = trailingslashit( $loc['url'] ) . $mscf_dirbase;
		if ( is_file( $dir . basename( __FILE__ ) ) ) {
			$mscf_dir = $dir;
			$mscf_url = $url;
			break;
		}
	}

	// Define our complete filesystem path
	define( 'MCSF_DIR', $mscf_dir );

	/**
	 * Lang location needs to be relative *from* ABSPATH,
	 * so strip it out of our language dir location
	 */
	define( 'MCSF_LANG_DIR', trailingslashit( MCSF_DIR ) . 'po/' );

	// Define our complete URL to the plugin folder
	define( 'MCSF_URL', $mscf_url );
}


/**
 * MODIFIED VERSION of wp_verify_nonce from WP Core. Core was not overridden to prevent problems when replacing
 * something universally.
 *
 * Verify that correct nonce was used with time limit.
 *
 * The user is given an amount of time to use the token, so therefore, since the
 * UID and $action remain the same, the independent variable is the time.
 *
 * @param string     $nonce Nonce that was used in the form to verify
 * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
 * @return bool Whether the nonce check passed or failed.
 */
function mailchimp_sf_verify_nonce( $nonce, $action = -1 ) {
	$user = wp_get_current_user();
	$uid  = (int) $user->ID;
	if ( ! $uid ) {
		$uid = apply_filters( 'nonce_user_logged_out', $uid, $action );
	}

	if ( empty( $nonce ) ) {
		return false;
	}

	$token = 'MAILCHIMP';
	$i     = wp_nonce_tick();

	// Nonce generated 0-12 hours ago
	$expected = substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
	if ( hash_equals( $expected, $nonce ) ) {
		return 1;
	}

	// Nonce generated 12-24 hours ago
	$expected = substr( wp_hash( ( $i - 1 ) . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
	if ( hash_equals( $expected, $nonce ) ) {
		return 2;
	}

	// Invalid nonce
	return false;
}


/**
 * MODIFIED VERSION of wp_create_nonce from WP Core. Core was not overridden to prevent problems when replacing
 * something universally.
 *
 * Creates a cryptographic token tied to a specific action, user, and window of time.
 *
 * @param string $action Scalar value to add context to the nonce.
 * @return string The token.
 */
function mailchimp_sf_create_nonce( $action = -1 ) {
	$user = wp_get_current_user();
	$uid  = (int) $user->ID;
	if ( ! $uid ) {
		/** This filter is documented in wp-includes/pluggable.php */
		$uid = apply_filters( 'nonce_user_logged_out', $uid, $action );
	}

	$token = 'MAILCHIMP';
	$i     = wp_nonce_tick();

	return substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
}
