<?php
/**
 * Class responsible for admin side functionalities.
 *
 * The long term plan is to break up admin functionality into smaller, more focused
 * files to improve maintainability. This could also include:
 * - Moving OAuth related code to oauth.php
 * - Moving account creation code to account.php
 * - Moving settings page code to settings.php
 * - Moving notices code to notices.php (already done)
 * This will help avoid having too much code in a single file and make the codebase more modular.
 *
 * @package Mailchimp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mailchimp_Admin
 *
 * @since 1.6.0
 */
class Mailchimp_Admin {

	/**
	 * The OAuth base endpoint
	 *
	 * @since 1.6.0
	 * @var string
	 */
	private $oauth_url = 'https://wordpress.mailchimpapp.com';

	/**
	 * Initialize the class
	 */
	public function init() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'wp_ajax_mailchimp_sf_oauth_start', array( $this, 'start_oauth_process' ) );
		add_action( 'wp_ajax_mailchimp_sf_oauth_finish', array( $this, 'finish_oauth_process' ) );
		add_action( 'wp_ajax_mailchimp_sf_create_account', array( $this, 'mailchimp_create_account' ) );
		add_action( 'wp_ajax_mailchimp_sf_check_login_session', array( $this, 'check_login_session' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_page_scripts' ) );
		add_action( 'admin_menu', array( $this, 'add_create_account_page' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ) );
	}

	/**
	 * Start the OAuth process.
	 *
	 * This function is called via AJAX.
	 *
	 * It starts the OAuth process by the calling the OAuth middleware
	 * server and sending the response to the front-end.
	 */
	public function start_oauth_process() {
		// Validate the nonce and permissions.
		if (
			! current_user_can( 'manage_options' ) ||
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mailchimp_sf_oauth_start_nonce' )
		) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to perform this action.', 'mailchimp' ) ) );
		}

		// Generate a secret and send it to the OAuth server.
		$secret = uniqid( 'mailchimp_sf_' );
		$args   = array(
			'domain' => site_url(),
			'secret' => $secret,
		);

		$options = array(
			'headers' => array(
				'Content-type' => 'application/json',
			),
			'body'    => wp_json_encode( $args ),
		);

		$response = wp_remote_post( $this->oauth_url . '/api/start', $options );

		// Check for errors.
		if ( $response instanceof WP_Error ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		// Send the response to the front-end.
		if ( 201 === $response['response']['code'] && ! empty( $response['body'] ) ) {
			set_site_transient( 'mailchimp_sf_oauth_secret', $secret, 60 * 60 );
			$result = json_decode( $response['body'], true );
			wp_send_json_success( $result );
		} else {
			if ( ! empty( $response['response'] ) ) {
				$response = $response['response'];
			}
			wp_send_json_error( $response );
		}
	}

	/**
	 * Finish the OAuth process.
	 *
	 * This function is called via AJAX.
	 *
	 * This function finishes the OAuth process by the sending
	 * a temporary token back to the OAuth server.
	 */
	public function finish_oauth_process() {
		// Validate the nonce and permissions.
		if (
			! current_user_can( 'manage_options' ) ||
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mailchimp_sf_oauth_finish_nonce' )
		) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to perform this action.', 'mailchimp' ) ) );
		}

		$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
		$args  = array(
			'domain' => site_url(),
			'secret' => get_site_transient( 'mailchimp_sf_oauth_secret' ),
			'token'  => $token,
		);

		$options  = array(
			'headers' => array(
				'Content-type' => 'application/json',
			),
			'body'    => wp_json_encode( $args ),
		);
		$response = wp_remote_post( $this->oauth_url . '/api/finish', $options );

		// Check for errors.
		if ( $response instanceof WP_Error ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		if ( 200 === $response['response']['code'] ) {
			// Save the access token and data center.
			$result = json_decode( $response['body'], true );
			if ( $result && ! empty( $result['access_token'] ) && ! empty( $result['data_center'] ) ) {
				delete_site_transient( 'mailchimp_sf_oauth_secret' );

				// Verify the token.
				$verify = $this->verify_and_save_oauth_token( $result['access_token'], $result['data_center'] );

				if ( is_wp_error( $verify ) ) {
					// If there is an error, send it back to the front-end.
					wp_send_json_error( array( 'message' => $verify->get_error_message() ) );
				}

				wp_send_json_success( true );
			} else {
				wp_send_json_error( array( 'message' => esc_html__( 'Invalid response from the server.', 'mailchimp' ) ) );
			}
		} else {
			wp_send_json_error( $response );
		}
	}

	/**
	 * Create a new Mailchimp account.
	 *
	 * This function is called via AJAX.
	 *
	 * This function creates a new Mailchimp account by sending the user data to the middleware server.
	 */
	public function mailchimp_create_account() {
		// Validate the nonce and permissions.
		if (
			! current_user_can( 'manage_options' ) ||
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mailchimp_sf_create_account_nonce' )
		) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to perform this action.', 'mailchimp' ) ) );
		}

		$data = isset( $_POST['data'] ) ? $this->sanitize_data( wp_unslash( $_POST['data'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is sanitized in the sanitize_data method.
		if ( empty( $data ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'No data provided.', 'mailchimp' ) ) );
		}

		// Get the IP address.
		if ( isset( $_SERVER['REMOTE_ADDR'] ) && ( '::1' === $_SERVER['REMOTE_ADDR'] || '127.0.0.1' === $_SERVER['REMOTE_ADDR'] ) ) {
			$data['ip_address'] = '127.0.0.1';
		} elseif ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$data['ip_address'] = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$data['ip_address'] = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} else {
			$data['ip_address'] = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		$request_data = array(
			'headers' => array(
				'Content-type' => 'application/json',
				'Accept'       => 'application/json',
			),
			'body'    => wp_json_encode( $data ),
			'timeout' => 30,
		);

		$response = wp_remote_post( $this->oauth_url . '/api/signup/', $request_data );
		// Return the error if there is one.
		if ( $response instanceof WP_Error ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		$response_body = json_decode( $response['body'] );
		if ( 200 === $response['response']['code'] && true === $response_body->success ) {
			$result = json_decode( $response['body'], true );

			// Verify and save the token.
			$verify = $this->verify_and_save_oauth_token( $result['data']['oauth_token'], $result['data']['dc'] );

			if ( is_wp_error( $verify ) ) {
				// If there is an error, send it back to the front-end.
				wp_send_json_error( array( 'message' => $verify->get_error_message() ) );
			}
			update_option( 'mailchimp_sf_waiting_for_login', 'waiting' );
			wp_send_json_success( true );

		} elseif ( 404 === $response['response']['code'] ) {
			wp_send_json_error( array( 'success' => false ) );

		} else {
			$username           = isset( $_POST['data']['username'] ) ? sanitize_email( wp_unslash( $_POST['data']['username'] ) ) : '';
			$username           = preg_replace( '/[^A-Za-z0-9\-\@\.]/', '', $username );
			$suggestion         = wp_remote_get( $this->oauth_url . '/api/usernames/suggestions/' . $username );
			$suggested_username = json_decode( $suggestion['body'] )->data;
			wp_send_json_error(
				array(
					'success'            => false,
					'suggest_login'      => true,
					'suggested_username' => $suggested_username,
				)
			);
		}
	}

	/**
	 * Check the login session.
	 *
	 * This function is called via AJAX.
	 *
	 * This function checks if the user is logged in to Mailchimp which confirms the account activation.
	 */
	public function check_login_session() {
		// Validate the nonce and permissions.
		if (
			! current_user_can( 'manage_options' ) ||
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mailchimp_sf_check_login_session_nonce' )
		) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to perform this action.', 'mailchimp' ) ) );
		}

		$api = mailchimp_sf_get_api();
		if ( $api ) {
			$profile = $api->get( '' );
			if ( is_wp_error( $profile ) ) {
				wp_send_json_error( array( 'message' => $profile->get_error_message() ) );
			}

			$logged_in = ( ! empty( $profile['last_login'] ) );
			if ( $logged_in ) {
				delete_option( 'mailchimp_sf_waiting_for_login' );
			}
			wp_send_json_success(
				array(
					'success'   => true,
					'logged_in' => $logged_in,
					'redirect'  => admin_url( 'admin.php?page=mailchimp_sf_options' ),
				)
			);
		} else {
			wp_send_json_error( array( 'success' => false ) );
		}
	}

	/**
	 * Verify and save the OAuth token.
	 *
	 * @param string $access_token The token to verify.
	 * @param string $data_center  The data center to verify.
	 * @return mixed
	 */
	public function verify_and_save_oauth_token( $access_token, $data_center ) {
		try {
			$api = new MailChimp_API( $access_token, $data_center );
		} catch ( Exception $e ) {
			$msg = $e->getMessage();
			return new WP_Error( 'mailchimp-sf-invalid-token', $msg );
		}

		$user = $api->get( '' );
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		// Might as well set this data if we have it already.
		$valid_roles = array( 'owner', 'admin', 'manager' );
		if ( isset( $user['role'] ) && in_array( $user['role'], $valid_roles, true ) ) {
			$data_encryption = new Mailchimp_Data_Encryption();

			// Clean up the old data.
			delete_option( 'mc_api_key' ); // Deprecated API key, need to remove as part of the migration.
			delete_option( 'mailchimp_sf_access_token' );
			delete_option( 'mailchimp_sf_auth_error' );
			delete_option( 'mc_datacenter' );

			update_option( 'mailchimp_sf_access_token', $data_encryption->encrypt( $access_token ) );
			update_option( 'mc_datacenter', sanitize_text_field( $data_center ) );
			update_option( 'mc_user', $this->sanitize_data( $user ) );

			// Clear Mailchimp List ID if saved list is not available.
			$lists = $api->get( 'lists', 100, array( 'fields' => 'lists.id,lists.name,lists.email_type_option' ) );
			if ( ! is_wp_error( $lists ) ) {
				$lists         = $lists['lists'] ?? array();
				$saved_list_id = get_option( 'mc_list_id' );
				$list_ids      = array_map(
					function ( $ele ) {
						return $ele['id'];
					},
					$lists
				);
				if ( ! in_array( $saved_list_id, $list_ids, true ) ) {
					delete_option( 'mc_list_id' );
				}

				// Update lists option.
				if ( ! empty( $lists ) ) {
					update_option( 'mailchimp_sf_lists', $lists );
				}
			}
			return true;
		} else {
			$msg = esc_html__( 'API Key must belong to "Owner", "Admin", or "Manager."', 'mailchimp' );
			return new WP_Error( 'mailchimp-sf-invalid-role', $msg );
		}
	}

	/**
	 * Display admin notices.
	 *
	 * @since 1.6.0
	 */
	public function admin_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$current_screen = get_current_screen();

		// Display a deprecation notice if the user is using an API key to connect with Mailchimp.
		if ( get_option( 'mc_api_key', '' ) && ! get_option( 'mailchimp_sf_access_token', '' ) && mailchimp_sf_should_display_form() ) {

			if ( $current_screen && 'toplevel_page_mailchimp_sf_options' === $current_screen->id ) {
				?>
				<div class="notice notice-warning">
					<p>
						<?php
						esc_html_e( 'You are using an outdated API Key connection to Mailchimp, please migrate to the new OAuth authentication method to continue accessing your Mailchimp account.', 'mailchimp' );
						?>
					</p>
					<div class="migrate-to-oauth-wrapper">
						<?php
						// Migrate button.
						$login_button_text = __( 'Migrate to OAuth authentication', 'mailchimp' );
						include_once MCSF_DIR . 'includes/admin/templates/login-button.php'; // phpcs:ignore PEAR.Files.IncludingFile.UseRequireOnce
						?>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="notice notice-warning is-dismissible">
					<p>
						<?php
						$message = sprintf(
							/* translators: Placeholders: %1$s - <a> tag, %2$s - </a> tag */
							__( 'You are using an outdated API Key connection to Mailchimp, please migrate to the new OAuth authentication method to continue accessing your Mailchimp account by clicking the "Migrate to OAuth authentication" button on the %1$sMailchimp settings%2$s page.', 'mailchimp' ),
							'<a href="' . esc_url( admin_url( 'admin.php?page=mailchimp_sf_options' ) ) . '">',
							'</a>'
						);

						echo wp_kses( $message, array( 'a' => array( 'href' => array() ) ) );
						?>
					</p>
				</div>
				<?php
			}
		}

		// Display a notice if the user is waiting for the login to complete.
		if ( $current_screen && 'toplevel_page_mailchimp_sf_options' === $current_screen->id ) {
			$api = mailchimp_sf_get_api();
			if ( $api && 'waiting' === get_option( 'mailchimp_sf_waiting_for_login' ) ) {
				$profile = $api->get( '' );
				if ( ! is_wp_error( $profile ) ) {
					if ( ! empty( $profile['last_login'] ) ) {
						// Clear the waiting flag if the user is logged in.
						delete_option( 'mailchimp_sf_waiting_for_login' );
					} else {
						?>
						<div class="notice notice-warning is-dismissible">
							<p>
								<?php
								esc_html_e( 'Please activate your Mailchimp account to complete the setup. Without activation, the connection to WordPress may be interrupted.', 'mailchimp' );
								?>
							</p>
						</div>
						<?php
					}
				}
			}
		}

		if (
			! get_option( 'mailchimp_sf_auth_error', false ) ||
			! get_option( 'mailchimp_sf_access_token', '' )
		) {
			return;
		}

		// Display a notice if the access token is invalid/revoked.
		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<?php
				$message = sprintf(
					/* translators: Placeholders: %1$s - <a> tag, %2$s - </a> tag */
					__( 'Heads up! There may be a problem with your connection to Mailchimp. Please %1$sre-connect%2$s your Mailchimp account to fix the issue.', 'mailchimp' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=mailchimp_sf_options' ) ) . '">',
					'</a>'
				);

				echo wp_kses( $message, array( 'a' => array( 'href' => array() ) ) );
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Sanitize variables using sanitize_text_field.
	 *
	 * Arrays are sanitized recursively, non-scalar values are ignored.
	 *
	 * @param string|array $data Data to sanitize.
	 * @return string|array
	 */
	public function sanitize_data( $data ) {
		if ( is_array( $data ) ) {
			return array_map( array( $this, 'sanitize_data' ), $data );
		} else {
			return is_scalar( $data ) ? sanitize_text_field( $data ) : $data;
		}
	}

	/**
	 * Enqueue scripts/styles for the Mailchimp admin page
	 *
	 * @param string $hook_suffix The current admin page.
	 * @return void
	 */
	public function enqueue_admin_page_scripts( $hook_suffix ) {
		if ( 'toplevel_page_mailchimp_sf_options' !== $hook_suffix && 'admin_page_mailchimp_sf_create_account' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'mailchimp_sf_admin_css', MCSF_URL . 'assets/css/admin.css', array( 'wp-jquery-ui-dialog' ), true );
		wp_enqueue_script( 'showMe', MCSF_URL . 'assets/js/hidecss.js', array( 'jquery' ), MCSF_VER, true );
		wp_enqueue_script( 'mailchimp_sf_admin', MCSF_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-dialog' ), MCSF_VER, true );

		$data = array(
			'ajax_url'               => esc_url( admin_url( 'admin-ajax.php' ) ),
			'oauth_url'              => esc_url( $this->oauth_url ),
			'oauth_start_nonce'      => wp_create_nonce( 'mailchimp_sf_oauth_start_nonce' ),
			'oauth_finish_nonce'     => wp_create_nonce( 'mailchimp_sf_oauth_finish_nonce' ),
			'oauth_window_name'      => esc_html__( 'Mailchimp For WordPress OAuth', 'mailchimp' ),
			'generic_error'          => esc_html__( 'An error occurred. Please try again.', 'mailchimp' ),
			'modal_title'            => esc_html__( 'Login Popup is blocked!', 'mailchimp' ),
			'modal_button_try_again' => esc_html__( 'Try again', 'mailchimp' ),
			'modal_button_cancel'    => esc_html__( 'No, cancel!', 'mailchimp' ),
			'admin_settings_url'     => esc_url( admin_url( 'admin.php?page=mailchimp_sf_options' ) ),
		);

		// Create account page specific data.
		if ( 'admin_page_mailchimp_sf_create_account' === $hook_suffix ) {
			$data['create_account_nonce']      = wp_create_nonce( 'mailchimp_sf_create_account_nonce' );
			$data['check_login_session_nonce'] = wp_create_nonce( 'mailchimp_sf_check_login_session_nonce' );
			/* translators: %s is field name. */
			$data['required_error']       = esc_html__( '%s  can\'t be blank.', 'mailchimp' );
			$data['invalid_email_error']  = esc_html__( 'Insert correct email.', 'mailchimp' );
			$data['confirm_email_match']  = esc_html__( 'Email confirmation must match confirmation email.', 'mailchimp' );
			$data['confirm_email_match2'] = esc_html__( 'Email confirmation must match the field above.', 'mailchimp' );
		}

		wp_localize_script(
			'mailchimp_sf_admin',
			'mailchimp_sf_admin_params',
			$data
		);
	}

	/**
	 * Add the create account page.
	 *
	 * @since 1.6.0
	 */
	public function add_create_account_page() {
		add_submenu_page(
			'admin.php',
			esc_html__( 'Create Mailchimp Account', 'mailchimp' ),
			esc_html__( 'Create Mailchimp Account', 'mailchimp' ),
			'manage_options',
			'mailchimp_sf_create_account',
			array( $this, 'create_account_page' )
		);
	}

	/**
	 * Create account page.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function create_account_page() {
		$countries = $this->get_countries();
		$timezones = $this->get_timezones();
		?>
		<div id="mailchimp-sf-settings-page">
			<?php
			include_once MCSF_DIR . 'includes/admin/templates/create-account-page.php';
			?>
		</div>
		<?php
	}

	/**
	 * Get a list of timezones.
	 *
	 * @since 1.6.0
	 *
	 * @return array
	 */
	private function get_timezones() {
		return timezone_identifiers_list();
	}

	/**
	 * Get a list of countries.
	 *
	 * @since 1.6.0
	 *
	 * @return array
	 */
	public function get_countries() {
		return array(
			'AF' => __( 'Afghanistan', 'mailchimp' ),
			'AX' => __( 'Åland Islands', 'mailchimp' ),
			'AL' => __( 'Albania', 'mailchimp' ),
			'DZ' => __( 'Algeria', 'mailchimp' ),
			'AS' => __( 'American Samoa', 'mailchimp' ),
			'AD' => __( 'Andorra', 'mailchimp' ),
			'AO' => __( 'Angola', 'mailchimp' ),
			'AI' => __( 'Anguilla', 'mailchimp' ),
			'AQ' => __( 'Antarctica', 'mailchimp' ),
			'AG' => __( 'Antigua and Barbuda', 'mailchimp' ),
			'AR' => __( 'Argentina', 'mailchimp' ),
			'AM' => __( 'Armenia', 'mailchimp' ),
			'AW' => __( 'Aruba', 'mailchimp' ),
			'AU' => __( 'Australia', 'mailchimp' ),
			'AT' => __( 'Austria', 'mailchimp' ),
			'AZ' => __( 'Azerbaijan', 'mailchimp' ),
			'BS' => __( 'Bahamas', 'mailchimp' ),
			'BH' => __( 'Bahrain', 'mailchimp' ),
			'BD' => __( 'Bangladesh', 'mailchimp' ),
			'BB' => __( 'Barbados', 'mailchimp' ),
			'BY' => __( 'Belarus', 'mailchimp' ),
			'BE' => __( 'Belgium', 'mailchimp' ),
			'PW' => __( 'Belau', 'mailchimp' ),
			'BZ' => __( 'Belize', 'mailchimp' ),
			'BJ' => __( 'Benin', 'mailchimp' ),
			'BM' => __( 'Bermuda', 'mailchimp' ),
			'BT' => __( 'Bhutan', 'mailchimp' ),
			'BO' => __( 'Bolivia', 'mailchimp' ),
			'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'mailchimp' ),
			'BA' => __( 'Bosnia and Herzegovina', 'mailchimp' ),
			'BW' => __( 'Botswana', 'mailchimp' ),
			'BV' => __( 'Bouvet Island', 'mailchimp' ),
			'BR' => __( 'Brazil', 'mailchimp' ),
			'IO' => __( 'British Indian Ocean Territory', 'mailchimp' ),
			'BN' => __( 'Brunei', 'mailchimp' ),
			'BG' => __( 'Bulgaria', 'mailchimp' ),
			'BF' => __( 'Burkina Faso', 'mailchimp' ),
			'BI' => __( 'Burundi', 'mailchimp' ),
			'KH' => __( 'Cambodia', 'mailchimp' ),
			'CM' => __( 'Cameroon', 'mailchimp' ),
			'CA' => __( 'Canada', 'mailchimp' ),
			'CV' => __( 'Cape Verde', 'mailchimp' ),
			'KY' => __( 'Cayman Islands', 'mailchimp' ),
			'CF' => __( 'Central African Republic', 'mailchimp' ),
			'TD' => __( 'Chad', 'mailchimp' ),
			'CL' => __( 'Chile', 'mailchimp' ),
			'CN' => __( 'China', 'mailchimp' ),
			'CX' => __( 'Christmas Island', 'mailchimp' ),
			'CC' => __( 'Cocos (Keeling) Islands', 'mailchimp' ),
			'CO' => __( 'Colombia', 'mailchimp' ),
			'KM' => __( 'Comoros', 'mailchimp' ),
			'CG' => __( 'Congo (Brazzaville)', 'mailchimp' ),
			'CD' => __( 'Congo (Kinshasa)', 'mailchimp' ),
			'CK' => __( 'Cook Islands', 'mailchimp' ),
			'CR' => __( 'Costa Rica', 'mailchimp' ),
			'HR' => __( 'Croatia', 'mailchimp' ),
			'CU' => __( 'Cuba', 'mailchimp' ),
			'CW' => __( 'Cura&ccedil;ao', 'mailchimp' ),
			'CY' => __( 'Cyprus', 'mailchimp' ),
			'CZ' => __( 'Czech Republic', 'mailchimp' ),
			'DK' => __( 'Denmark', 'mailchimp' ),
			'DJ' => __( 'Djibouti', 'mailchimp' ),
			'DM' => __( 'Dominica', 'mailchimp' ),
			'DO' => __( 'Dominican Republic', 'mailchimp' ),
			'EC' => __( 'Ecuador', 'mailchimp' ),
			'EG' => __( 'Egypt', 'mailchimp' ),
			'SV' => __( 'El Salvador', 'mailchimp' ),
			'GQ' => __( 'Equatorial Guinea', 'mailchimp' ),
			'ER' => __( 'Eritrea', 'mailchimp' ),
			'EE' => __( 'Estonia', 'mailchimp' ),
			'ET' => __( 'Ethiopia', 'mailchimp' ),
			'FK' => __( 'Falkland Islands', 'mailchimp' ),
			'FO' => __( 'Faroe Islands', 'mailchimp' ),
			'FJ' => __( 'Fiji', 'mailchimp' ),
			'FI' => __( 'Finland', 'mailchimp' ),
			'FR' => __( 'France', 'mailchimp' ),
			'GF' => __( 'French Guiana', 'mailchimp' ),
			'PF' => __( 'French Polynesia', 'mailchimp' ),
			'TF' => __( 'French Southern Territories', 'mailchimp' ),
			'GA' => __( 'Gabon', 'mailchimp' ),
			'GM' => __( 'Gambia', 'mailchimp' ),
			'GE' => __( 'Georgia', 'mailchimp' ),
			'DE' => __( 'Germany', 'mailchimp' ),
			'GH' => __( 'Ghana', 'mailchimp' ),
			'GI' => __( 'Gibraltar', 'mailchimp' ),
			'GR' => __( 'Greece', 'mailchimp' ),
			'GL' => __( 'Greenland', 'mailchimp' ),
			'GD' => __( 'Grenada', 'mailchimp' ),
			'GP' => __( 'Guadeloupe', 'mailchimp' ),
			'GU' => __( 'Guam', 'mailchimp' ),
			'GT' => __( 'Guatemala', 'mailchimp' ),
			'GG' => __( 'Guernsey', 'mailchimp' ),
			'GN' => __( 'Guinea', 'mailchimp' ),
			'GW' => __( 'Guinea-Bissau', 'mailchimp' ),
			'GY' => __( 'Guyana', 'mailchimp' ),
			'HT' => __( 'Haiti', 'mailchimp' ),
			'HM' => __( 'Heard Island and McDonald Islands', 'mailchimp' ),
			'HN' => __( 'Honduras', 'mailchimp' ),
			'HK' => __( 'Hong Kong', 'mailchimp' ),
			'HU' => __( 'Hungary', 'mailchimp' ),
			'IS' => __( 'Iceland', 'mailchimp' ),
			'IN' => __( 'India', 'mailchimp' ),
			'ID' => __( 'Indonesia', 'mailchimp' ),
			'IR' => __( 'Iran', 'mailchimp' ),
			'IQ' => __( 'Iraq', 'mailchimp' ),
			'IE' => __( 'Ireland', 'mailchimp' ),
			'IM' => __( 'Isle of Man', 'mailchimp' ),
			'IL' => __( 'Israel', 'mailchimp' ),
			'IT' => __( 'Italy', 'mailchimp' ),
			'CI' => __( 'Ivory Coast', 'mailchimp' ),
			'JM' => __( 'Jamaica', 'mailchimp' ),
			'JP' => __( 'Japan', 'mailchimp' ),
			'JE' => __( 'Jersey', 'mailchimp' ),
			'JO' => __( 'Jordan', 'mailchimp' ),
			'KZ' => __( 'Kazakhstan', 'mailchimp' ),
			'KE' => __( 'Kenya', 'mailchimp' ),
			'KI' => __( 'Kiribati', 'mailchimp' ),
			'KW' => __( 'Kuwait', 'mailchimp' ),
			'KG' => __( 'Kyrgyzstan', 'mailchimp' ),
			'LA' => __( 'Laos', 'mailchimp' ),
			'LV' => __( 'Latvia', 'mailchimp' ),
			'LB' => __( 'Lebanon', 'mailchimp' ),
			'LS' => __( 'Lesotho', 'mailchimp' ),
			'LR' => __( 'Liberia', 'mailchimp' ),
			'LY' => __( 'Libya', 'mailchimp' ),
			'LI' => __( 'Liechtenstein', 'mailchimp' ),
			'LT' => __( 'Lithuania', 'mailchimp' ),
			'LU' => __( 'Luxembourg', 'mailchimp' ),
			'MO' => __( 'Macao', 'mailchimp' ),
			'MK' => __( 'North Macedonia', 'mailchimp' ),
			'MG' => __( 'Madagascar', 'mailchimp' ),
			'MW' => __( 'Malawi', 'mailchimp' ),
			'MY' => __( 'Malaysia', 'mailchimp' ),
			'MV' => __( 'Maldives', 'mailchimp' ),
			'ML' => __( 'Mali', 'mailchimp' ),
			'MT' => __( 'Malta', 'mailchimp' ),
			'MH' => __( 'Marshall Islands', 'mailchimp' ),
			'MQ' => __( 'Martinique', 'mailchimp' ),
			'MR' => __( 'Mauritania', 'mailchimp' ),
			'MU' => __( 'Mauritius', 'mailchimp' ),
			'YT' => __( 'Mayotte', 'mailchimp' ),
			'MX' => __( 'Mexico', 'mailchimp' ),
			'FM' => __( 'Micronesia', 'mailchimp' ),
			'MD' => __( 'Moldova', 'mailchimp' ),
			'MC' => __( 'Monaco', 'mailchimp' ),
			'MN' => __( 'Mongolia', 'mailchimp' ),
			'ME' => __( 'Montenegro', 'mailchimp' ),
			'MS' => __( 'Montserrat', 'mailchimp' ),
			'MA' => __( 'Morocco', 'mailchimp' ),
			'MZ' => __( 'Mozambique', 'mailchimp' ),
			'MM' => __( 'Myanmar', 'mailchimp' ),
			'NA' => __( 'Namibia', 'mailchimp' ),
			'NR' => __( 'Nauru', 'mailchimp' ),
			'NP' => __( 'Nepal', 'mailchimp' ),
			'NL' => __( 'Netherlands', 'mailchimp' ),
			'NC' => __( 'New Caledonia', 'mailchimp' ),
			'NZ' => __( 'New Zealand', 'mailchimp' ),
			'NI' => __( 'Nicaragua', 'mailchimp' ),
			'NE' => __( 'Niger', 'mailchimp' ),
			'NG' => __( 'Nigeria', 'mailchimp' ),
			'NU' => __( 'Niue', 'mailchimp' ),
			'NF' => __( 'Norfolk Island', 'mailchimp' ),
			'MP' => __( 'Northern Mariana Islands', 'mailchimp' ),
			'KP' => __( 'North Korea', 'mailchimp' ),
			'NO' => __( 'Norway', 'mailchimp' ),
			'OM' => __( 'Oman', 'mailchimp' ),
			'PK' => __( 'Pakistan', 'mailchimp' ),
			'PS' => __( 'Palestinian Territory', 'mailchimp' ),
			'PA' => __( 'Panama', 'mailchimp' ),
			'PG' => __( 'Papua New Guinea', 'mailchimp' ),
			'PY' => __( 'Paraguay', 'mailchimp' ),
			'PE' => __( 'Peru', 'mailchimp' ),
			'PH' => __( 'Philippines', 'mailchimp' ),
			'PN' => __( 'Pitcairn', 'mailchimp' ),
			'PL' => __( 'Poland', 'mailchimp' ),
			'PT' => __( 'Portugal', 'mailchimp' ),
			'PR' => __( 'Puerto Rico', 'mailchimp' ),
			'QA' => __( 'Qatar', 'mailchimp' ),
			'RE' => __( 'Reunion', 'mailchimp' ),
			'RO' => __( 'Romania', 'mailchimp' ),
			'RU' => __( 'Russia', 'mailchimp' ),
			'RW' => __( 'Rwanda', 'mailchimp' ),
			'BL' => __( 'Saint Barth&eacute;lemy', 'mailchimp' ),
			'SH' => __( 'Saint Helena', 'mailchimp' ),
			'KN' => __( 'Saint Kitts and Nevis', 'mailchimp' ),
			'LC' => __( 'Saint Lucia', 'mailchimp' ),
			'MF' => __( 'Saint Martin (French part)', 'mailchimp' ),
			'SX' => __( 'Saint Martin (Dutch part)', 'mailchimp' ),
			'PM' => __( 'Saint Pierre and Miquelon', 'mailchimp' ),
			'VC' => __( 'Saint Vincent and the Grenadines', 'mailchimp' ),
			'SM' => __( 'San Marino', 'mailchimp' ),
			'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'mailchimp' ),
			'SA' => __( 'Saudi Arabia', 'mailchimp' ),
			'SN' => __( 'Senegal', 'mailchimp' ),
			'RS' => __( 'Serbia', 'mailchimp' ),
			'SC' => __( 'Seychelles', 'mailchimp' ),
			'SL' => __( 'Sierra Leone', 'mailchimp' ),
			'SG' => __( 'Singapore', 'mailchimp' ),
			'SK' => __( 'Slovakia', 'mailchimp' ),
			'SI' => __( 'Slovenia', 'mailchimp' ),
			'SB' => __( 'Solomon Islands', 'mailchimp' ),
			'SO' => __( 'Somalia', 'mailchimp' ),
			'ZA' => __( 'South Africa', 'mailchimp' ),
			'GS' => __( 'South Georgia/Sandwich Islands', 'mailchimp' ),
			'KR' => __( 'South Korea', 'mailchimp' ),
			'SS' => __( 'South Sudan', 'mailchimp' ),
			'ES' => __( 'Spain', 'mailchimp' ),
			'LK' => __( 'Sri Lanka', 'mailchimp' ),
			'SD' => __( 'Sudan', 'mailchimp' ),
			'SR' => __( 'Suriname', 'mailchimp' ),
			'SJ' => __( 'Svalbard and Jan Mayen', 'mailchimp' ),
			'SZ' => __( 'Eswatini', 'mailchimp' ),
			'SE' => __( 'Sweden', 'mailchimp' ),
			'CH' => __( 'Switzerland', 'mailchimp' ),
			'SY' => __( 'Syria', 'mailchimp' ),
			'TW' => __( 'Taiwan', 'mailchimp' ),
			'TJ' => __( 'Tajikistan', 'mailchimp' ),
			'TZ' => __( 'Tanzania', 'mailchimp' ),
			'TH' => __( 'Thailand', 'mailchimp' ),
			'TL' => __( 'Timor-Leste', 'mailchimp' ),
			'TG' => __( 'Togo', 'mailchimp' ),
			'TK' => __( 'Tokelau', 'mailchimp' ),
			'TO' => __( 'Tonga', 'mailchimp' ),
			'TT' => __( 'Trinidad and Tobago', 'mailchimp' ),
			'TN' => __( 'Tunisia', 'mailchimp' ),
			'TR' => __( 'Turkey', 'mailchimp' ),
			'TM' => __( 'Turkmenistan', 'mailchimp' ),
			'TC' => __( 'Turks and Caicos Islands', 'mailchimp' ),
			'TV' => __( 'Tuvalu', 'mailchimp' ),
			'UG' => __( 'Uganda', 'mailchimp' ),
			'UA' => __( 'Ukraine', 'mailchimp' ),
			'AE' => __( 'United Arab Emirates', 'mailchimp' ),
			'GB' => __( 'United Kingdom (UK)', 'mailchimp' ),
			'US' => __( 'United States (US)', 'mailchimp' ),
			'UM' => __( 'United States (US) Minor Outlying Islands', 'mailchimp' ),
			'UY' => __( 'Uruguay', 'mailchimp' ),
			'UZ' => __( 'Uzbekistan', 'mailchimp' ),
			'VU' => __( 'Vanuatu', 'mailchimp' ),
			'VA' => __( 'Vatican', 'mailchimp' ),
			'VE' => __( 'Venezuela', 'mailchimp' ),
			'VN' => __( 'Vietnam', 'mailchimp' ),
			'VG' => __( 'Virgin Islands (British)', 'mailchimp' ),
			'VI' => __( 'Virgin Islands (US)', 'mailchimp' ),
			'WF' => __( 'Wallis and Futuna', 'mailchimp' ),
			'EH' => __( 'Western Sahara', 'mailchimp' ),
			'WS' => __( 'Samoa', 'mailchimp' ),
			'YE' => __( 'Yemen', 'mailchimp' ),
			'ZM' => __( 'Zambia', 'mailchimp' ),
			'ZW' => __( 'Zimbabwe', 'mailchimp' ),
		);
	}

	/**
	 * Display the Mailchimp footer text on the Mailchimp admin pages.
	 *
	 * @since 1.6.0
	 *
	 * @param string $text The current footer text.
	 * @return string The modified footer text.
	 */
	public function admin_footer_text( $text ) {
		$current_screen    = get_current_screen();
		$current_screen_id = $current_screen ? $current_screen->id : '';
		if ( ! in_array( $current_screen_id, array( 'toplevel_page_mailchimp_sf_options', 'admin_page_mailchimp_sf_create_account' ), true ) ) {
			return $text;
		}

		return wp_kses(
			sprintf(
				/* translators: %d - Current year, %s - Mailchimp legal links */
				__( '©%1$d Intuit Inc. All rights reserved. Mailchimp® is a registered trademark of The Rocket Science Group, <a href="%2$s" target="_blank" rel="noopener noreferrer">Cookie Preferences</a>, <a href="%3$s" target="_blank" rel="noopener noreferrer">Privacy</a>, and <a href="%4$s" target="_blank" rel="noopener noreferrer">Terms</a>.', 'mailchimp' ),
				gmdate( 'Y' ),
				esc_url( 'https://mailchimp.com/legal/cookies/#optanon-toggle-display/' ),
				esc_url( 'https://www.intuit.com/privacy/statement/' ),
				esc_url( 'https://mailchimp.com/legal/terms' )
			),
			array(
				'a' => array(
					'href'   => array(),
					'target' => array(),
					'rel'    => array(),
				),
			)
		);
	}
}
