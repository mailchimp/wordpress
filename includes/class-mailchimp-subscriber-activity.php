<?php
/**
 * Subscriber activity (new subscribers vs unsubscribes) data provider for the
 * Analytics page.
 *
 * Fetches list activity from the Mailchimp Marketing API
 * (`lists/{list_id}/activity`), caches the raw response in a transient, and
 * exposes an admin-only AJAX endpoint that returns the data filtered to the
 * requested date range and aggregated into daily / weekly / monthly /
 * quarterly / yearly buckets for the "Subscriber change over time" chart.
 *
 * @package Mailchimp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mailchimp_Subscriber_Activity
 */
class Mailchimp_Subscriber_Activity {

	use Mailchimp_Analytics_Bucketing;

	/**
	 * Transient key prefix for the cached raw API response.
	 */
	const CACHE_PREFIX = 'mailchimp_sf_subscriber_activity_';

	/**
	 * Transient TTL in seconds.
	 */
	const CACHE_TTL = 15 * MINUTE_IN_SECONDS;

	/**
	 * Hard cap enforced by the Mailchimp Activity API.
	 */
	const API_WINDOW_DAYS = 180;

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_ajax_mailchimp_sf_get_subscriber_activity', array( $this, 'handle_get' ) );
	}

	/**
	 * AJAX handler for `mailchimp_sf_get_subscriber_activity`.
	 *
	 * @return void
	 */
	public function handle_get() {
		if ( ! current_user_can( MCSF_CAP_THRESHOLD ) ) {
			wp_send_json_error(
				array(
					'message'    => esc_html__( 'Unauthorized.', 'mailchimp' ),
					'error_code' => 'unauthorized',
				),
				403
			);
		}

		check_ajax_referer( 'mailchimp_sf_analytics_admin_nonce', 'nonce' );

		$list_id   = isset( $_POST['list_id'] ) ? sanitize_text_field( wp_unslash( $_POST['list_id'] ) ) : '';
		$date_from = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
		$date_to   = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';

		if ( empty( $list_id ) ) {
			wp_send_json_error(
				array(
					'message'    => esc_html__( 'Please select a list.', 'mailchimp' ),
					'error_code' => 'invalid_request',
				),
				400
			);
		}

		if ( ! $this->is_valid_date( $date_from ) || ! $this->is_valid_date( $date_to ) ) {
			wp_send_json_error(
				array(
					'message'    => esc_html__( 'Invalid date range.', 'mailchimp' ),
					'error_code' => 'invalid_request',
				),
				400
			);
		}

		if ( strtotime( $date_from ) > strtotime( $date_to ) ) {
			wp_send_json_error(
				array(
					'message'    => esc_html__( 'Start date must be before end date.', 'mailchimp' ),
					'error_code' => 'invalid_request',
				),
				400
			);
		}

		$raw = $this->fetch_activity( $list_id );

		if ( is_wp_error( $raw ) ) {
			$is_disconnected = in_array(
				$raw->get_error_code(),
				array( 'mailchimp_sf_not_connected', 'mailchimp-auth-error' ),
				true
			);
			wp_send_json_error(
				array(
					'message'    => $raw->get_error_message(),
					'error_code' => $is_disconnected ? 'not_connected' : 'api_error',
				),
				$is_disconnected ? 401 : 502
			);
		}

		if ( ! is_array( $raw ) ) {
			wp_send_json_error(
				array(
					'message'    => esc_html__( 'Unable to reach Mailchimp.', 'mailchimp' ),
					'error_code' => 'api_error',
				),
				502
			);
		}

		$response = $this->filter_and_aggregate( $raw, $date_from, $date_to );

		wp_send_json_success( $response );
	}

	/**
	 * Fetch (and cache) the raw list activity for a list.
	 *
	 * The Mailchimp Activity API always returns the most recent 180 days for a
	 * given list regardless of query params, so a single transient can serve
	 * multiple date-range requests within its TTL.
	 *
	 * @param string $list_id List ID.
	 * @return array|WP_Error Normalized list of `{ date, new_subscribers, unsubscribes }` or error.
	 */
	public function fetch_activity( string $list_id ) {
		$cache_key = self::CACHE_PREFIX . md5( $list_id );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$api = mailchimp_sf_get_api();
		if ( ! $api ) {
			return new WP_Error(
				'mailchimp_sf_not_connected',
				esc_html__( 'Mailchimp account is not connected.', 'mailchimp' )
			);
		}

		$response = $api->get( 'lists/' . rawurlencode( $list_id ) . '/activity', self::API_WINDOW_DAYS );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! is_array( $response ) || ! isset( $response['activity'] ) || ! is_array( $response['activity'] ) ) {
			return new WP_Error(
				'mailchimp_sf_invalid_response',
				esc_html__( 'Unexpected response from Mailchimp.', 'mailchimp' )
			);
		}

		$normalized = array();
		foreach ( $response['activity'] as $row ) {
			if ( empty( $row['day'] ) ) {
				continue;
			}
			$normalized[] = array(
				'date'            => sanitize_text_field( $row['day'] ),
				'new_subscribers' => isset( $row['subs'] ) ? (int) $row['subs'] : 0,
				'unsubscribes'    => isset( $row['unsubs'] ) ? (int) $row['unsubs'] : 0,
			);
		}

		set_transient( $cache_key, $normalized, self::CACHE_TTL );

		return $normalized;
	}

	/**
	 * Filter the raw (180 day) dataset to the selected range and aggregate it
	 * into buckets appropriate for that range.
	 *
	 * When the requested range exceeds the API's 180-day window we intersect
	 * with `[today - 179, today]` (inclusive, site timezone) and flag the
	 * response as `limited`.
	 *
	 * @param array  $raw       Normalized rows from `fetch_activity()`.
	 * @param string $date_from `Y-m-d`.
	 * @param string $date_to   `Y-m-d`.
	 * @return array Response payload.
	 */
	public function filter_and_aggregate( array $raw, string $date_from, string $date_to ): array {
		$tz         = wp_timezone();
		$from_dt    = new DateTimeImmutable( $date_from, $tz );
		$to_dt      = new DateTimeImmutable( $date_to, $tz );
		$today      = ( new DateTimeImmutable( 'now', $tz ) )->setTime( 0, 0, 0 );
		$api_oldest = $today->modify( '-' . ( self::API_WINDOW_DAYS - 1 ) . ' days' );

		$requested_days = (int) $from_dt->diff( $to_dt )->days + 1;
		$limited        = $requested_days > self::API_WINDOW_DAYS;

		$effective_from = $from_dt < $api_oldest ? $api_oldest : $from_dt;
		$effective_to   = $to_dt > $today ? $today : $to_dt;

		$interval = $this->get_interval( $requested_days );

		$buckets      = array();
		$total_new    = 0;
		$total_unsubs = 0;

		if ( $effective_from <= $effective_to ) {
			// Index API rows by date so missing days can be back-filled with zeros.
			// The Mailchimp Activity API omits days with no subscribe/unsubscribe
			// activity, which would otherwise leave gaps in the chart timeline.
			$by_date = array();
			foreach ( $raw as $row ) {
				$by_date[ $row['date'] ] = $row;
			}

			$one_day = new DateInterval( 'P1D' );
			$cursor  = $effective_from;

			while ( $cursor <= $effective_to ) {
				$date            = $cursor->format( 'Y-m-d' );
				$new_subscribers = isset( $by_date[ $date ]['new_subscribers'] )
					? (int) $by_date[ $date ]['new_subscribers']
					: 0;
				$unsubscribes    = isset( $by_date[ $date ]['unsubscribes'] )
					? (int) $by_date[ $date ]['unsubscribes']
					: 0;

				$key = $this->get_bucket_key( $date, $interval, $tz );
				if ( ! isset( $buckets[ $key ] ) ) {
					$buckets[ $key ] = array(
						'key'             => $key,
						'label'           => $this->get_bucket_label( $date, $interval, $tz ),
						'new_subscribers' => 0,
						'unsubscribes'    => 0,
					);
				}

				$buckets[ $key ]['new_subscribers'] += $new_subscribers;
				$buckets[ $key ]['unsubscribes']    += $unsubscribes;

				$total_new    += $new_subscribers;
				$total_unsubs += $unsubscribes;

				$cursor = $cursor->add( $one_day );
			}

			ksort( $buckets );
		}

		return array(
			'interval'     => $interval,
			'data'         => array_values( $buckets ),
			'total_new'    => $total_new,
			'total_unsubs' => $total_unsubs,
			'net_change'   => $total_new - $total_unsubs,
			'limited'      => $limited,
		);
	}
}
