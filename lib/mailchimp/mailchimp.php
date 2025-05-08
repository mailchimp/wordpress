<?php
/**
 * Handles Mailchimp API authorization.
 *
 * @package Mailchimp
 */

/**
 * Handles Mailchimp API authorization.
 */
class MailChimp_API {

	/**
	 * The access token.
	 *
	 * @var string
	 */
	public $access_token;

	/**
	 * The API key
	 *
	 * @var string
	 */
	public $key;

	/**
	 * The API url
	 *
	 * @var string
	 */
	public $api_url;

	/**
	 * The datacenter.
	 *
	 * @var string
	 */
	public $datacenter;

	/**
	 * Initialize the class
	 *
	 * @param  string $access_token Access token or API key. If data center is not provided, we'll assume that this is an API key.
	 * @param  string $data_center  The data center. If not provided, we'll assume the data center is in the API key itself.
	 * @throws Exception If no api key or access token is set
	 */
	public function __construct( $access_token, $data_center = '' ) {
		$access_token = trim( $access_token );
		if ( ! $access_token ) {
			throw new Exception(
				esc_html(
					sprintf(
						/* translators: %s: access token */
						__( 'Invalid Access Token or API key: %s', 'mailchimp' ),
						$access_token
					)
				)
			);
		}

		// No data center provided, so we'll assume it's in the API key.
		if ( ! $data_center ) {
			$this->key        = $access_token;
			$dc               = explode( '-', $access_token );
			$this->datacenter = empty( $dc[1] ) ? 'us1' : $dc[1];
		} else {
			$this->access_token = $access_token;
			$this->datacenter   = $data_center;
		}

		$this->api_url = 'https://' . $this->datacenter . '.api.mailchimp.com/3.0/';
	}

	/**
	 * Get endpoint.
	 *
	 * @param string  $endpoint The Mailchimp endpoint.
	 * @param integer $count The count to retrieve.
	 * @param array   $fields The fields to retrieve.
	 * @return mixed
	 */
	public function get( $endpoint, $count = 10, $fields = array() ) {
		$query_params = '';

		$url = $this->api_url . $endpoint;

		if ( $count ) {
			$query_params = 'count=' . $count . '&';
		}

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field => $value ) {
				$query_params .= $field . '=' . $value . '&';
			}
		}

		if ( $query_params ) {
			$url .= "?{$query_params}";
		}

		$headers = array();
		// If we have an access token, use that, otherwise use the API key.
		if ( $this->access_token ) {
			$headers['Authorization'] = 'Bearer ' . $this->access_token;
		} else {
			$headers['Authorization'] = 'apikey ' . $this->key;
		}

		$args = array(
			'timeout'     => 10,
			'redirection' => 5,
			'httpversion' => '1.1',
			'user-agent'  => 'Mailchimp WordPress Plugin/' . get_bloginfo( 'url' ),
			'headers'     => $headers,
		);

		$request = wp_remote_get( $url, $args );

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		if ( is_array( $request ) && 200 === $request['response']['code'] ) {
			delete_option( 'mailchimp_sf_auth_error' );
			return json_decode( $request['body'], true );
		} elseif ( is_array( $request ) && $request['response']['code'] ) {
			// Check if Access Token is invalid/revoked.
			if ( in_array( $request['response']['code'], array( 401, 403 ), true ) ) {
				update_option( 'mailchimp_sf_auth_error', true );
				return new WP_Error( 'mailchimp-auth-error', esc_html__( 'Authentication failed.', 'mailchimp' ) );
			}

			$error = json_decode( $request['body'], true );
			$error = new WP_Error( 'mailchimp-get-error', $error['detail'] ?? esc_html__( 'Something went wrong, Please try again later.', 'mailchimp' ) );
			return $error;
		} else {
			return false;
		}
	}

	/**
	 * Sends request to Mailchimp endpoint.
	 *
	 * @param string $endpoint The endpoint to send the request.
	 * @param string $body The body of the request
	 * @param string $method The request method.
	 * @param string $list_id The list id.
	 * @return mixed
	 */
	public function post( $endpoint, $body, $method = 'POST', $list_id = '' ) {
		$url = $this->api_url . $endpoint;

		$headers = array();
		// If we have an access token, use that, otherwise use the API key.
		if ( $this->access_token ) {
			$headers['Authorization'] = 'Bearer ' . $this->access_token;
		} else {
			$headers['Authorization'] = 'apikey ' . $this->key;
		}

		$args    = array(
			'method'      => $method,
			'timeout'     => 10,
			'redirection' => 5,
			'httpversion' => '1.1',
			'user-agent'  => 'Mailchimp WordPress Plugin/' . get_bloginfo( 'url' ),
			'headers'     => $headers,
			'body'        => wp_json_encode( $body ),
		);
		$request = wp_remote_post( $url, $args );

		if ( is_array( $request ) && 200 === $request['response']['code'] ) {
			delete_option( 'mailchimp_sf_auth_error' );
			return json_decode( $request['body'], true );
		} else {
			if ( is_wp_error( $request ) ) {
				return new WP_Error( 'mc-subscribe-error', $request->get_error_message() );
			}

			// Check if Access Token is invalid/revoked.
			if ( is_array( $request ) && in_array( $request['response']['code'], array( 401, 403 ), true ) ) {
				update_option( 'mailchimp_sf_auth_error', true );
			}

			$body   = json_decode( $request['body'], true );
			$merges = get_option( 'mc_merge_vars' );
			// Get merge fields for the list if we have a list id.
			if ( ! empty( $list_id ) ) {
				$merges = get_option( 'mailchimp_sf_merge_fields_' . $list_id );

				// If we don't have merge fields for the list, get the default merge fields.
				if ( empty( $merges ) ) {
					$merges = get_option( 'mc_merge_vars' );
				}
			}

			// Check if the email address is in compliance state.
			if ( ! isset( $body['errors'] ) && isset( $body['status'] ) && isset( $body['title'] ) && 400 === $body['status'] && 'Member In Compliance State' === $body['title'] ) {
				$url     = mailchimp_sf_signup_form_url( $list_id );
				$message = wp_kses(
					sprintf(
						/* translators: %s: Hosted form Url */
						__(
							'The email address cannot be subscribed because it was previously unsubscribed, bounced, or is under review. Please <a href="%s" target="_blank">sign up here.</a>',
							'mailchimp'
						),
						esc_url( $url )
					),
					[
						'a' => [
							'href'   => [],
							'target' => [],
						],
					]
				);
				return new WP_Error( 'mc-subscribe-error-compliance', $message );
			}

			$field_name = '';
			foreach ( $merges as $merge ) {
				if ( empty( $body['errors'] ) ) {
					// Email address doesn't come back from the API, so if something's wrong, it's that.
					$field_name                   = esc_html__( 'Email Address', 'mailchimp' );
					$body['errors'][0]['message'] = esc_html__( 'Please fill out a valid email address.', 'mailchimp' );
				} elseif ( ! empty( $body['errors'] ) && isset( $body['errors'][0]['field'] ) && 'email_address' === $body['errors'][0]['field'] ) {
					$field_name = esc_html__( 'Email Address', 'mailchimp' );
				} elseif ( ! empty( $body['errors'] ) && isset( $body['errors'][0]['field'] ) && $merge['tag'] === $body['errors'][0]['field'] ) {
					$field_name = $merge['name'];
				}
			}
			$message = $body['errors'][0]['message'] ?? esc_html__( 'Something went wrong, Please try again later.', 'mailchimp' );
			$message = ( ! empty( $field_name ) ) ? $field_name . ': ' . $message : $message;

			return new WP_Error( 'mc-subscribe-error-api', $message );
		}
	}
}
