<?php
/**
 * Class responsible for handling the form submission for the Mailchimp block.
 *
 * @package Mailchimp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mailchimp_Form_Submission
 *
 * @since 1.8.0
 */
class Mailchimp_Form_Submission {

	/**
	 * Initialize the class.
	 */
	public function init() {
		// TODO: Update this to use ajax handler hook instead of init.
		add_action( 'init', array( $this, 'request_handler' ) );
	}

	/**
	 * Request handler
	 *
	 * @return void
	 */
	public function request_handler() {
		// Check if we have a request to handle.
		if ( ! isset( $_POST['mcsf_action'] ) ) {
			return;
		}

		// Check for correct action.
		if ( 'mc_submit_signup_form' !== sanitize_text_field( wp_unslash( $_POST['mcsf_action'] ) ) ) {
			return;
		}

		// Validate nonce.
		if (
			! isset( $_POST['_mc_submit_signup_form_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['_mc_submit_signup_form_nonce'] ), 'mc_submit_signup_form' )
		) {
			wp_die( 'Cheatin&rsquo; huh?' );
		}

		// Handle form submission.
		$response    = $this->handle_form_submission();
		$submit_type = isset( $_POST['mc_submit_type'] ) ? sanitize_text_field( wp_unslash( $_POST['mc_submit_type'] ) ) : '';

		// If we have an error, then show it.
		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
			mailchimp_sf_global_msg( '<strong class="mc_error_msg">' . $error . '</strong>' );
		} else {
			mailchimp_sf_global_msg( '<strong class="mc_success_msg">' . esc_html( $response ) . '</strong>' );
		}

		// Do a different action for html vs. js
		switch ( $submit_type ) {
			case 'html':
				/* This gets set elsewhere! */
				break;
			case 'js':
				if ( ! headers_sent() ) { // just in case...
					header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT', true, 200 );
				}
				// TODO: Refactor this to use JSON response instead of setting a global message.
				echo wp_kses_post( mailchimp_sf_global_msg() );
				exit;
		}
	}

	/**
	 * Handles the form submission for the Mailchimp form.
	 *
	 * @return string|WP_Error Success message or error.
	 */
	public function handle_form_submission() {
		$is_valid = $this->validate_form_submission();
		if ( is_wp_error( $is_valid ) || ! $is_valid ) {
			if ( is_wp_error( $is_valid ) ) {
				return $is_valid;
			}

			// If the form submission is invalid, return an error.
			return new WP_Error( 'mailchimp-invalid-form', esc_html__( 'Invalid form submission.', 'mailchimp' ) );
		}

		$list_id         = get_option( 'mc_list_id' );
		$update_existing = get_option( 'mc_update_existing' );
		$double_opt_in   = get_option( 'mc_double_optin' );
		$merge_fields    = get_option( 'mc_merge_vars', array() );
		$interest_groups = get_option( 'mc_interest_groups', array() );

		// Check if request from latest block.
		if ( isset( $_POST['mailchimp_sf_list_id'] ) ) {
			$list_id         = isset( $_POST['mailchimp_sf_list_id'] ) ? sanitize_text_field( wp_unslash( $_POST['mailchimp_sf_list_id'] ) ) : '';
			$update_existing = isset( $_POST['mailchimp_sf_update_existing_subscribers'] ) ? sanitize_text_field( wp_unslash( $_POST['mailchimp_sf_update_existing_subscribers'] ) ) : '';
			$double_opt_in   = isset( $_POST['mailchimp_sf_double_opt_in'] ) ? sanitize_text_field( wp_unslash( $_POST['mailchimp_sf_double_opt_in'] ) ) : '';
			$hash            = isset( $_POST['mailchimp_sf_hash'] ) ? sanitize_text_field( wp_unslash( $_POST['mailchimp_sf_hash'] ) ) : '';
			$expected        = wp_hash(
				serialize( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
					array(
						'list_id'         => $list_id,
						'update_existing' => $update_existing,
						'double_opt_in'   => $double_opt_in,
					)
				)
			);

			// Bail if the hash is invalid.
			if ( ! hash_equals( $expected, $hash ) ) {
				return new WP_Error( 'mailchimp-invalid-hash', esc_html__( 'Invalid form submission.', 'mailchimp' ) );
			}

			$update_existing = 'yes' === $update_existing;
			$double_opt_in   = 'yes' === $double_opt_in;
			$merge_fields    = get_option( 'mailchimp_sf_merge_fields_' . $list_id, array() );
			$interest_groups = get_option( 'mailchimp_sf_interest_groups_' . $list_id, array() );
		}

		// Prepare request body
		$email             = isset( $_POST['mc_mv_EMAIL'] ) ? wp_strip_all_tags( wp_unslash( $_POST['mc_mv_EMAIL'] ) ) : '';
		$merge_fields_body = $this->prepare_merge_fields_body( $merge_fields );

		// Catch errors and fail early.
		if ( is_wp_error( $merge_fields_body ) ) {
			return $merge_fields_body;
		}

		$interest_groups = ! is_array( $interest_groups ) ? array() : $interest_groups;
		$groups          = $this->prepare_groups_body( $interest_groups );

		// Clear out empty merge fields.
		$merge_fields_body = $this->remove_empty_merge_fields( $merge_fields_body );
		if ( isset( $_POST['email_type'] ) && in_array( $_POST['email_type'], array( 'text', 'html', 'mobile' ), true ) ) {
			$email_type = sanitize_text_field( wp_unslash( $_POST['email_type'] ) );
		} else {
			$email_type = 'html';
		}

		$response = $this->subscribe_to_list(
			$list_id,
			$email,
			array(
				'email_type'      => $email_type,
				'merge_fields'    => $merge_fields_body,
				'interests'       => $groups,
				'update_existing' => $update_existing,
				'double_opt_in'   => $double_opt_in,
			)
		);

		// If we have errors, then show them
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$message = '';
		if ( 'subscribed' === $response['status'] ) {
			$message = __( 'Success, you\'ve been signed up.', 'mailchimp' );
		} else {
			$message = __( 'Success, you\'ve been signed up! Please look for our confirmation email.', 'mailchimp' );
		}

		// Return success message.
		return $message;
	}

	/**
	 * Prepare the merge fields body for the API request.
	 *
	 * @param array $merge_fields Merge fields.
	 * @return stdClass|WP_Error
	 */
	public function prepare_merge_fields_body( $merge_fields ) {
		// Loop through our merge fields, and if they're empty, but required, then print an error, and mark as failed
		$merge = new stdClass();
		foreach ( $merge_fields as $merge_field ) {
			$tag = $merge_field['tag'];
			$opt = 'mc_mv_' . $tag;

			// Skip if the field is not required and not submitted.
			if ( 'Y' !== $merge_field['required'] && ! isset( $_POST[ $opt ] ) ) {
				continue;
			}

			$opt_val = isset( $_POST[ $opt ] ) ? map_deep( stripslashes_deep( $_POST[ $opt ] ), 'sanitize_text_field' ) : '';

			switch ( $merge_field['type'] ) {
				/**
				 * US Phone validation
				 *
				 * - Merge field is phone
				 * - Phone format is set in Mailchimp account
				 * - Phone format is US in Mailchimp account
				 */
				case 'phone':
					if (
						isset( $merge_field['options']['phone_format'] )
						&& 'US' === $merge_field['options']['phone_format']
					) {
						$opt_val = mailchimp_sf_merge_validate_phone( $opt_val, $merge_field );
						if ( is_wp_error( $opt_val ) ) {
							return $opt_val;
						}
					}
					break;

				/**
				 * Address validation
				 *
				 * - Merge field is address
				 * - Merge field is an array (address contains multiple <input> elements)
				 */
				case 'address':
					if ( is_array( $opt_val ) ) {
						$validate = mailchimp_sf_merge_validate_address( $opt_val, $merge_field );
						if ( is_wp_error( $validate ) ) {
							return $validate;
						}

						if ( $validate ) {
							$merge->$tag = $validate;
						}
					}
					break;

				/**
				 * Handle generic array values
				 *
				 * Not sure what this does or is for
				 *
				 * - Merge field is an array, not specifically phone or address
				 */
				default:
					if ( is_array( $opt_val ) ) {
						$keys = array_keys( $opt_val );
						$val  = new stdClass();
						foreach ( $keys as $key ) {
							$val->$key = $opt_val[ $key ];
						}
						$opt_val = $val;
					}
					break;
			}

			/**
			 * Required fields
			 *
			 * If the field is required and empty, return an error
			 */
			if ( 'Y' === $merge_field['required'] && trim( $opt_val ) === '' ) {
				/* translators: %s: field name */
				$message = sprintf( esc_html__( 'You must fill in %s.', 'mailchimp' ), esc_html( $merge_field['name'] ) );
				$error   = new WP_Error( 'missing_required_field', $message );
				return $error;
			} elseif ( 'EMAIL' !== $tag ) {
				$merge->$tag = $opt_val;
			}
		}
		return $merge;
	}

	/**
	 * Prepare the interest groups body for the API request.
	 *
	 * @param array $interest_groups Interest groups.
	 * @return stdClass
	 */
	public function prepare_groups_body( $interest_groups ) {
		// Bail if we don't have any interest groups
		if ( empty( $interest_groups ) ) {
			return new stdClass();
		}

		$groups = $this->set_all_groups_to_false( $interest_groups );

		foreach ( $interest_groups as $interest_group ) {
			$ig_id = $interest_group['id'];
			if ( isset( $_POST['group'][ $ig_id ] ) && 'hidden' !== $interest_group['type'] ) {
				switch ( $interest_group['type'] ) {
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
									stripslashes_deep( $_POST['group'][ $ig_id ] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- ignoring because this is sanitized through array_map above
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
	 * Set all interest groups to false.
	 *
	 * @param array $interest_groups Interest groups.
	 * @return stdClass
	 */
	public function set_all_groups_to_false( $interest_groups ) {
		$groups = new stdClass();

		foreach ( $interest_groups as $interest_group ) {
			if ( 'hidden' !== $interest_group['type'] ) {
				foreach ( $interest_group['groups'] as $group ) {
					$id          = $group['id'];
					$groups->$id = false;
				}
			}
		}

		return $groups;
	}

	/**
	 * Get signup form URL for the Mailchimp list.
	 *
	 * @param string $list_id The list ID.
	 * @return string
	 */
	public function get_signup_form_url( $list_id ) {
		$dc   = get_option( 'mc_datacenter' );
		$user = get_option( 'mc_user' );
		$url  = 'https://' . $dc . '.list-manage.com/subscribe?u=' . $user['account_id'] . '&id=' . $list_id;
		return $url;
	}

	/**
	 * Check the status of a subscriber.
	 *
	 * @param string $list_id The list ID.
	 * @param string $email   The email address of the subscriber.
	 * @return string|bool The status of the subscriber or false on error.
	 */
	public function get_subscriber_status( $list_id, $email ) {
		$api = mailchimp_sf_get_api();
		if ( ! $api ) {
			return false;
		}

		$endpoint   = 'lists/' . $list_id . '/members/' . md5( strtolower( $email ) ) . '?fields=status';
		$subscriber = $api->get( $endpoint, null );
		if ( is_wp_error( $subscriber ) ) {
			return false;
		}
		return $subscriber['status'];
	}

	/**
	 * Subscribe to a list.
	 *
	 * @param string $list_id The list ID.
	 * @param string $email   The email address of the subscriber.
	 * @param array  $args    Additional arguments for the subscription.
	 *
	 * @return WP_Error|array The response from the Mailchimp API or an error.
	 */
	protected function subscribe_to_list( $list_id, $email, $args ) {
		$api = mailchimp_sf_get_api();
		// If we don't have an API, then show an error message.
		if ( ! $api ) {
			$url   = $this->get_signup_form_url( $list_id );
			$error = wp_kses(
				sprintf(
					/* translators: 1: email address 2: url */
					__(
						'We encountered a problem adding %1$s to the list. Please <a href="%2$s">sign up here.</a>',
						'mailchimp'
					),
					esc_html( $email ),
					esc_url( $url )
				),
				[
					'a' => [
						'href' => [],
					],
				]
			);
			return new WP_Error( 'mailchimp-auth-error', $error );
		}

		$url    = 'lists/' . $list_id . '/members/' . md5( strtolower( $email ) );
		$status = $this->get_subscriber_status( $list_id, $email );

		// If update existing is turned off and the subscriber is not new, error out.
		$is_new_subscriber = false === $status;
		if ( ! $args['update_existing'] && ! $is_new_subscriber ) {
			$msg = esc_html__( 'This email address has already been subscribed to this list.', 'mailchimp' );
			return new WP_Error( 'mailchimp-update-existing', $msg );
		}

		// Prepare request body
		$request_body = $this->prepare_subscribe_request_body( $email, $status, $args );
		$response     = $api->post( $url, $request_body, 'PUT', $list_id );

		return $response;
	}

	/**
	 * Prepare the request body for the Mailchimp API.
	 *
	 * @param string $email   The email address of the subscriber.
	 * @param string $status  The status of the subscriber (e.g., subscribed, pending).
	 * @param array  $args    Additional arguments for the subscription, including:
	 *                        - merge_fields (array): Merge fields data.
	 *                        - interests (array): Interest groups data.
	 *                        - email_type (string): The type of email (e.g., html, text).
	 *                        - double_opt_in (bool): Whether to use double opt-in.
	 *                        - update_existing (bool): Whether to update existing subscribers.
	 *
	 * @return stdClass The prepared request body.
	 */
	protected function prepare_subscribe_request_body( $email, $status, $args ) {
		// Prepare the request body for the Mailchimp API.
		$request_body                = new stdClass();
		$request_body->email_address = $email;
		$request_body->email_type    = $args['email_type'];
		$request_body->merge_fields  = $args['merge_fields'];

		if ( ! empty( $args['interests'] ) ) {
			$request_body->interests = $args['interests'];
		}

		// Early return for already subscribed users
		if ( 'subscribed' === $status ) {
			return $request_body;
		}

		// Subscribe the email immediately unless double opt-in is enabled
		// "unsubscribed" and "subscribed" existing emails have been excluded at this stage
		// "pending" emails should follow double opt-in rules
		$request_body->status = $args['double_opt_in'] ? 'pending' : 'subscribed';

		return $request_body;
	}

	/**
	 * Remove empty merge fields from the request body.
	 *
	 * @param object $merge Merge fields request body.
	 * @return object The modified merge fields request body.
	 */
	public function remove_empty_merge_fields( $merge ) {
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
	 * Validate the form submission.
	 * Basic checks for the prevention of spam.
	 *
	 * @return bool|WP_Error True if valid, WP_Error if invalid.
	 */
	protected function validate_form_submission() {
		$spam_message = esc_html__( "We couldn't process your submission as it was flagged as potential spam. Please try again.", 'mailchimp' );
		// Make sure the honeypot field is set, but not filled (if it is, then it's a spam).
		if ( ! isset( $_POST['mailchimp_sf_alt_email'] ) || ! empty( $_POST['mailchimp_sf_alt_email'] ) ) {
			return new WP_Error( 'spam', $spam_message );
		}

		// Make sure that no-js field is not present (if it is, then it's a spam).
		if ( isset( $_POST['mailchimp_sf_no_js'] ) ) {
			return new WP_Error( 'spam', $spam_message );
		}

		// Make sure that user-agent is set and it has reasonable length.
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		if ( strlen( $user_agent ) < 2 ) {
			return new WP_Error( 'spam', $spam_message );
		}

		/**
		 * Filter to allow for custom validation of the form submission.
		 *
		 * @since 1.8.0
		 * @param bool  $is_valid  True if valid, false if invalid, return WP_Error to provide error message.
		 * @param array $post_data The $_POST data.
		 */
		return apply_filters( 'mailchimp_sf_form_submission_validation', true, $_POST );
	}
}
