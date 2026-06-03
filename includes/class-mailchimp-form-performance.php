<?php
/**
 * Form performance (list-level submissions over time) data provider for the
 * Analytics page.
 *
 * @package Mailchimp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mailchimp_Form_Performance
 */
class Mailchimp_Form_Performance {

	use Mailchimp_Analytics_Bucketing;

	/**
	 * Transient key prefix for the cached total subscriber count.
	 */
	const SUBSCRIBERS_CACHE_PREFIX = 'mailchimp_sf_total_subscribers_';

	/**
	 * Transient TTL for the cached total subscriber count.
	 */
	const SUBSCRIBERS_CACHE_TTL = 15 * MINUTE_IN_SECONDS;

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_ajax_mailchimp_sf_get_form_performance', array( $this, 'handle_get' ) );
	}

	/**
	 * AJAX handler for `mailchimp_sf_get_form_performance`.
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

		$rows = $this->fetch_rows( $list_id, $date_from, $date_to );

		if ( null === $rows ) {
			wp_send_json_error(
				array(
					'message'    => esc_html__( 'An internal error occurred while loading analytics. Please contact support if this persists.', 'mailchimp' ),
					'error_code' => 'db_error',
				),
				500
			);
		}

		$response = $this->aggregate( $rows, $date_from, $date_to );

		$response['total_subscribers'] = $this->fetch_total_subscribers( $list_id );

		wp_send_json_success( $response );
	}

	/**
	 * Fetch (and cache) the current total subscriber count for a list.
	 *
	 * @param string $list_id List ID.
	 * @return int|null
	 */
	public function fetch_total_subscribers( string $list_id ): ?int {
		$cache_key = self::SUBSCRIBERS_CACHE_PREFIX . md5( $list_id );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return (int) $cached;
		}

		$api = mailchimp_sf_get_api();
		if ( ! $api ) {
			return null;
		}

		$response = $api->get(
			'lists/' . rawurlencode( $list_id ),
			1,
			array( 'stats.member_count' )
		);

		if ( is_wp_error( $response ) || ! is_array( $response ) ) {
			return null;
		}

		if ( ! isset( $response['stats']['member_count'] ) ) {
			return null;
		}

		$count = (int) $response['stats']['member_count'];
		set_transient( $cache_key, $count, self::SUBSCRIBERS_CACHE_TTL );

		return $count;
	}

	/**
	 * Fetch daily totals from the analytics table for the selected list/range.
	 *
	 * @param string $list_id   List ID.
	 * @param string $date_from `Y-m-d`.
	 * @param string $date_to   `Y-m-d`.
	 * @return array|null Rows of `{ event_date, views, submissions }`, or null on DB error.
	 */
	public function fetch_rows( string $list_id, string $date_from, string $date_to ): ?array {
		global $wpdb;

		$table_name = Mailchimp_Analytics_Data::get_table_name();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT event_date, SUM(views) AS views, SUM(submissions) AS submissions
				FROM {$table_name}
				WHERE list_id = %s AND event_date BETWEEN %s AND %s
				GROUP BY event_date
				ORDER BY event_date ASC",
				$list_id,
				$date_from,
				$date_to
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( null === $results || ! empty( $wpdb->last_error ) ) {
			return null;
		}

		return is_array( $results ) ? $results : array();
	}

	/**
	 * Bucket daily rows into the chart's interval, back-filling missing days
	 * with zeros so every bucket on the x-axis has a value
	 *
	 * @param array  $rows      Rows from `fetch_rows()`.
	 * @param string $date_from `Y-m-d`.
	 * @param string $date_to   `Y-m-d`.
	 * @return array Response payload.
	 */
	public function aggregate( array $rows, string $date_from, string $date_to ): array {
		$tz      = wp_timezone();
		$from_dt = new DateTimeImmutable( $date_from, $tz );
		$to_dt   = new DateTimeImmutable( $date_to, $tz );

		$requested_days = (int) $from_dt->diff( $to_dt )->days + 1;
		$interval       = $this->get_interval( $requested_days );

		// Build the complete ordered set of bucket keys covering the range.
		$buckets = array();
		$one_day = new DateInterval( 'P1D' );
		$cursor  = $from_dt;
		while ( $cursor <= $to_dt ) {
			$date = $cursor->format( 'Y-m-d' );
			$key  = $this->get_bucket_key( $date, $interval, $tz );
			if ( ! isset( $buckets[ $key ] ) ) {
				$buckets[ $key ] = array(
					'key'         => $key,
					'label'       => $this->get_bucket_label( $date, $interval, $tz ),
					'views'       => 0,
					'submissions' => 0,
				);
			}
			$cursor = $cursor->add( $one_day );
		}
		ksort( $buckets );

		$total_views       = 0;
		$total_submissions = 0;

		foreach ( $rows as $row ) {
			$date        = isset( $row['event_date'] ) ? (string) $row['event_date'] : '';
			$views       = isset( $row['views'] ) ? (int) $row['views'] : 0;
			$submissions = isset( $row['submissions'] ) ? (int) $row['submissions'] : 0;

			if ( '' === $date ) {
				continue;
			}

			$key = $this->get_bucket_key( $date, $interval, $tz );
			if ( ! isset( $buckets[ $key ] ) ) {
				continue;
			}

			$buckets[ $key ]['views']       += $views;
			$buckets[ $key ]['submissions'] += $submissions;
			$total_views                    += $views;
			$total_submissions              += $submissions;
		}

		$data = array();
		foreach ( $buckets as $bucket ) {
			$bucket['conversion_rate'] = $this->conversion_rate( $bucket['submissions'], $bucket['views'] );
			$data[]                    = $bucket;
		}

		return array(
			'interval'              => $interval,
			'data'                  => $data,
			'total_views'           => $total_views,
			'total_submissions'     => $total_submissions,
			'total_conversion_rate' => $this->conversion_rate( $total_submissions, $total_views ),
		);
	}
}
