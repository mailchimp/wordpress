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
	 * The API key
	 *
	 * @var string
	 */
	public $key;

	/**
	 * The API key
	 *
	 * @var string
	 */
	public $api_key;

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
	 * @param  string $api_key The API key.
	 * @throws Exception If no api key is set
	 */
	public function __construct( $api_key ) {
		$api_key = trim( $api_key );
		if ( ! $api_key ) {
			throw new Exception(
				esc_html(
					sprintf(
						/* translators: %s: api key */
						__( 'Invalid API Key: %s', 'mailchimp' ),
						$api_key
					)
				)
			);
		}

		$this->key        = $api_key;
		$dc               = explode( '-', $api_key );
		$this->datacenter = empty( $dc[1] ) ? 'us1' : $dc[1];
		$this->api_url    = 'https://' . $this->datacenter . '.api.mailchimp.com/3.0/';
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

		$args = array(
			'timeout'     => 5,
			'redirection' => 5,
			'httpversion' => '1.1',
			'user-agent'  => 'Mailchimp WordPress Plugin/' . get_bloginfo( 'url' ),
			'headers'     => array( 'Authorization' => 'apikey ' . $this->key ),
		);

		$request = wp_remote_get( $url, $args );

		if ( is_array( $request ) && 200 === $request['response']['code'] ) {
			return json_decode( $request['body'], true );
		} elseif ( is_array( $request ) && $request['response']['code'] ) {
			$error = json_decode( $request['body'], true );
			$error = new WP_Error( 'mailchimp-get-error', $error['detail'] );
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
	 * @return mixed
	 */
	public function post( $endpoint, $body, $method = 'POST' ) {
		$url = $this->api_url . $endpoint;

		$args    = array(
			'method'      => $method,
			'timeout'     => 5,
			'redirection' => 5,
			'httpversion' => '1.1',
			'user-agent'  => 'Mailchimp WordPress Plugin/' . get_bloginfo( 'url' ),
			'headers'     => array( 'Authorization' => 'apikey ' . $this->key ),
			'body'        => wp_json_encode( $body ),
		);
		$request = wp_remote_post( $url, $args );

		if ( is_array( $request ) && 200 === $request['response']['code'] ) {
			return json_decode( $request['body'], true );
		} else {
			if ( is_wp_error( $request ) ) {
				return new WP_Error( 'mc-subscribe-error', $request->get_error_message() );
			}

			$body       = json_decode( $request['body'], true );
			$merges     = get_option( 'mc_merge_vars' );
			$field_name = '';
			foreach ( $merges as $merge ) {
				if ( empty( $body['errors'] ) ) {
					// Email address doesn't come back from the API, so if something's wrong, it's that.
					$field_name                   = 'Email Address';
					$body['errors'][0]['message'] = 'Please fill out a valid email address.';
				} elseif ( $merge['tag'] === $body['errors'][0]['field'] ) {
					$field_name = $merge['name'];
				}
			}
			$message = sprintf( $field_name . ': ' . $body['errors'][0]['message'] );
			return new WP_Error( 'mc-subscribe-error-api', $message );
		}
	}
}
