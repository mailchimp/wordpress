<?php
/**
 * Form performance (per-form submissions over time) data provider for the
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
			wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized.', 'mailchimp' ) ), 403 );
		}

		check_ajax_referer( 'mailchimp_sf_analytics_admin_nonce', 'nonce' );

		$list_id   = isset( $_POST['list_id'] ) ? sanitize_text_field( wp_unslash( $_POST['list_id'] ) ) : '';
		$date_from = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
		$date_to   = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';

		if ( empty( $list_id ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Please select a list.', 'mailchimp' ) ), 400 );
		}

		if ( ! $this->is_valid_date( $date_from ) || ! $this->is_valid_date( $date_to ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid date range.', 'mailchimp' ) ), 400 );
		}

		if ( strtotime( $date_from ) > strtotime( $date_to ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Start date must be before end date.', 'mailchimp' ) ), 400 );
		}

		$rows = $this->fetch_rows( $list_id, $date_from, $date_to );

		if ( null === $rows ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unable to load form analytics.', 'mailchimp' ) ), 500 );
		}

		$response = $this->aggregate( $rows, $date_from, $date_to );

		wp_send_json_success( $response );
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

	/**
	 * Submissions ÷ views, as a percentage (0–100, two decimals).
	 *
	 * @param int $submissions Submission count.
	 * @param int $views       View count.
	 * @return float
	 */
	private function conversion_rate( int $submissions, int $views ): float {
		if ( $views <= 0 ) {
			return 0.0;
		}
		$rate = ( $submissions / $views ) * 100;
		return round( min( 100.0, $rate ), 2 );
	}

	/**
	 * Pick an aggregation interval based on the requested range.
	 *
	 * @param int $days Inclusive day count of the requested range.
	 * @return string One of `daily|weekly|monthly|quarterly|yearly`.
	 */
	public function get_interval( int $days ): string {
		if ( $days <= 30 ) {
			return 'daily';
		}
		if ( $days <= 90 ) {
			return 'weekly';
		}
		if ( $days <= 365 ) {
			return 'monthly';
		}
		if ( $days <= 365 * 3 ) {
			return 'quarterly';
		}
		return 'yearly';
	}

	/**
	 * Build a stable sort key for the bucket a given date falls into.
	 *
	 * @param string            $date     `Y-m-d`.
	 * @param string            $interval Interval name.
	 * @param DateTimeZone|null $tz       Timezone.
	 * @return string
	 */
	public function get_bucket_key( string $date, string $interval, $tz = null ): string {
		$tz = $tz instanceof DateTimeZone ? $tz : wp_timezone();
		$dt = new DateTimeImmutable( $date, $tz );

		switch ( $interval ) {
			case 'weekly':
				// ISO week starts on Monday.
				return $dt->format( 'o-\WW' );
			case 'monthly':
				return $dt->format( 'Y-m' );
			case 'quarterly':
				$quarter = (int) ceil( (int) $dt->format( 'n' ) / 3 );
				return $dt->format( 'Y' ) . '-Q' . $quarter;
			case 'yearly':
				return $dt->format( 'Y' );
			case 'daily':
			default:
				return $dt->format( 'Y-m-d' );
		}
	}

	/**
	 * Build a human-readable label for the bucket a given date falls into.
	 *
	 * @param string            $date     `Y-m-d`.
	 * @param string            $interval Interval name.
	 * @param DateTimeZone|null $tz       Timezone.
	 * @return string
	 */
	public function get_bucket_label( string $date, string $interval, $tz = null ): string {
		$tz = $tz instanceof DateTimeZone ? $tz : wp_timezone();
		$dt = new DateTimeImmutable( $date, $tz );

		switch ( $interval ) {
			case 'weekly':
				$monday = $dt->modify( 'monday this week' );
				if ( $monday > $dt ) {
					$monday = $dt->modify( 'monday last week' );
				}
				return wp_date( 'M j', $monday->getTimestamp(), $tz );
			case 'monthly':
				return wp_date( 'M Y', $dt->getTimestamp(), $tz );
			case 'quarterly':
				$quarter = (int) ceil( (int) $dt->format( 'n' ) / 3 );
				return 'Q' . $quarter . ' ' . $dt->format( 'Y' );
			case 'yearly':
				return $dt->format( 'Y' );
			case 'daily':
			default:
				return wp_date( 'M j', $dt->getTimestamp(), $tz );
		}
	}

	/**
	 * Validate a `Y-m-d` date string.
	 *
	 * @param string $date Candidate date string.
	 * @return bool
	 */
	private function is_valid_date( string $date ): bool {
		if ( '' === $date ) {
			return false;
		}
		$dt = DateTimeImmutable::createFromFormat( 'Y-m-d', $date );
		return $dt && $dt->format( 'Y-m-d' ) === $date;
	}
}
