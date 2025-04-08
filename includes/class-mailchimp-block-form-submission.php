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
 * Class Mailchimp_Block_Form_Submission
 *
 * @since 1.7.0
 */
class Mailchimp_Block_Form_Submission {

	/**
	 * Handles the form submission for the Mailchimp block.
	 *
	 * @return bool
	 */
	public function handle_form_submission() {
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
			$msg = '<strong class="mc_error_msg">' . esc_html__( 'Invalid form submission.', 'mailchimp' ) . '</strong>';
			mailchimp_sf_global_msg( $msg );
			return false;
		}

		// Prepare request body
		$merge_fields      = get_option( 'mailchimp_sf_merge_fields_' . $list_id, array() );
		$interest_groups   = get_option( 'mailchimp_sf_interest_groups_' . $list_id, array() );
		$email             = isset( $_POST['mc_mv_EMAIL'] ) ? wp_strip_all_tags( wp_unslash( $_POST['mc_mv_EMAIL'] ) ) : '';
		$merge_fields_body = $this->prepare_merge_fields_body( $merge_fields );

		// Catch errors and fail early.
		if ( is_wp_error( $merge_fields_body ) ) {
			$msg = '<strong class="mc_error_msg">' . $merge_fields_body->get_error_message() . '</strong>';
			mailchimp_sf_global_msg( $msg );

			return false;
		}

		$interest_groups = ! is_array( $interest_groups ) ? array() : $interest_groups;
		$groups          = $this->prepare_groups_body( $interest_groups );

		// Clear out empty merge vars
		$merge_fields_body = mailchimp_sf_merge_remove_empty( $merge_fields_body );
		if ( isset( $_POST['email_type'] ) && in_array( $_POST['email_type'], array( 'text', 'html', 'mobile' ), true ) ) {
			$email_type = sanitize_text_field( wp_unslash( $_POST['email_type'] ) );
		} else {
			$email_type = 'html';
		}

		$api = mailchimp_sf_get_api();
		// If we don't have an API, then show an error message.
		if ( ! $api ) {
			$url   = $this->get_signup_form_url( $list_id );
			$error = sprintf(
				'<strong class="mc_error_msg">%s</strong>',
				wp_kses(
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
				)
			);
			mailchimp_sf_global_msg( $error );
			return false;
		}

		$url    = 'lists/' . $list_id . '/members/' . md5( strtolower( $email ) );
		$status = mailchimp_sf_check_status( $url );

		// If update existing is turned off and the subscriber is not new, error out.
		$is_new_subscriber = false === $status;
		if ( 'yes' !== $update_existing && ! $is_new_subscriber ) {
			$msg   = esc_html__( 'This email address has already been subscribed to this list.', 'mailchimp' );
			$error = new WP_Error( 'mailchimp-update-existing', $msg );
			mailchimp_sf_global_msg( '<strong class="mc_error_msg">' . $msg . '</strong>' );
			return false;
		}

		$request_body = mailchimp_sf_subscribe_body( $merge_fields_body, $groups, $email_type, $email, $status, 'yes' === $double_opt_in );
		$response     = $api->post( $url, $request_body, 'PUT', $list_id );

		// If we have errors, then show them
		if ( is_wp_error( $response ) ) {
			$msg = '<strong class="mc_error_msg">' . $response->get_error_message() . '</strong>';
			mailchimp_sf_global_msg( $msg );
			return false;
		}

		if ( 'subscribed' === $response['status'] ) {
			$esc = esc_html__( 'Success, you\'ve been signed up.', 'mailchimp' );
			$msg = "<strong class='mc_success_msg'>{$esc}</strong>";
		} else {
			$esc = esc_html__( 'Success, you\'ve been signed up! Please look for our confirmation email.', 'mailchimp' );
			$msg = "<strong class='mc_success_msg'>{$esc}</strong>";
		}

		// Set our global message
		mailchimp_sf_global_msg( $msg );
		return true;
	}

	/**
	 * Prepare the merge fields body for the API request.
	 *
	 * @param array $merge_fields Merge fields.
	 * @return stdClass|WP_Error
	 */
	protected function prepare_merge_fields_body( $merge_fields ) {
		// Loop through our Merge Vars, and if they're empty, but required, then print an error, and mark as failed
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
	protected function prepare_groups_body( $interest_groups ) {
		// Bail if we don't have any interest groups
		if ( empty( $interest_groups ) ) {
			return new StdClass();
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
	 * Set all interest groups to false.
	 *
	 * @param array $interest_groups Interest groups.
	 * @return stdClass
	 */
	protected function set_all_groups_to_false( $interest_groups ) {
		$groups = new StdClass();

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
	protected function get_signup_form_url( $list_id ) {
		$dc   = get_option( 'mc_datacenter' );
		$user = get_option( 'mc_user' );
		$url  = 'https://' . $dc . '.list-manage.com/subscribe?u=' . $user['account_id'] . '&id=' . $list_id;
		return $url;
	}
}
