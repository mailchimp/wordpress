<?php
/**
 * Class responsible for Admin side functionalities.
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
 * @since x.x.x
 */
class Mailchimp_Admin {

	/**
	 * The OAuth base endpoint
	 *
	 * @since x.x.x
	 * @var string
	 */
	private $oauth_url = 'https://woocommerce.mailchimpapp.com';

	/**
	 * Initialize the class
	 */
	public function init() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'wp_ajax_mailchimp_sf_oauth_start', array( $this, 'start_oauth_process' ) );
		add_action( 'wp_ajax_mailchimp_sf_oauth_finish', array( $this, 'finish_oauth_process' ) );
	}


	/**
	 * Start the OAuth process.
	 *
	 * This function is called via AJAX.
	 * It starts the OAuth process by the calling the OAuth middleware server and sending the response to the front-end.
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
	 * This function finishes the OAuth process by the sending a temporary token back to the OAuth server.
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
			delete_option( 'mailchimp_sf_access_token' );
			delete_option( 'mailchimp_sf_auth_error' );
			delete_option( 'mc_datacenter' );

			update_option( 'mailchimp_sf_access_token', $data_encryption->encrypt( $access_token ) );
			update_option( 'mc_datacenter', sanitize_text_field( $data_center ) );
			update_option( 'mc_user', $this->sanitize_data( $user ) );
			return true;

		} else {
			$msg = esc_html__( 'API Key must belong to "Owner", "Admin", or "Manager."', 'mailchimp' );
			return new WP_Error( 'mailchimp-sf-invalid-role', $msg );
		}
	}

	/**
	 * Display admin notices.
	 *
	 * @since x.x.x
	 */
	public function admin_notices() {
		if (
			! get_option( 'mailchimp_sf_auth_error', false ) ||
			! current_user_can( 'manage_options' ) ||
			! get_option( 'mailchimp_sf_access_token', '' )
		) {
			return;
		}

		// display a notice if the access token is invalid/revoked.
		?>
		<div class="notice notice-warning is-dismissible">
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
	 * Arrays are sanitized recursively, Non-scalar values are ignored.
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
}
