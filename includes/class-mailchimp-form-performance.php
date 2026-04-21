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

		if ( ! is_array( $rows ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unable to load form analytics.', 'mailchimp' ) ), 500 );
		}

		$response = $this->aggregate( $rows, $date_from, $date_to );

		wp_send_json_success( $response );
	}

	/**
	 * Fetch raw daily rows from the analytics table grouped by form_id and date.
	 *
	 * @param string $list_id   List ID.
	 * @param string $date_from `Y-m-d`.
	 * @param string $date_to   `Y-m-d`.
	 * @return array Rows of `{ form_id, event_date, views, submissions }`.
	 */
	public function fetch_rows( string $list_id, string $date_from, string $date_to ): array {
		global $wpdb;

		$table_name = Mailchimp_Analytics_Data::get_table_name();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT form_id, event_date, SUM(views) AS views, SUM(submissions) AS submissions
				FROM {$table_name}
				WHERE list_id = %s AND event_date BETWEEN %s AND %s
				GROUP BY form_id, event_date
				ORDER BY event_date ASC",
				$list_id,
				$date_from,
				$date_to
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return is_array( $results ) ? $results : array();
	}

	/**
	 * Filter raw rows into per-form series bucketed by interval.
	 *
	 * Missing bucket/form intersections are back-filled with zeros so every
	 * series has the same length as the chart's x-axis — keeping line charts
	 * aligned even when a given form had no submissions on some days.
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

		// Build the complete ordered set of bucket keys covering the range so
		// every form series aligns with the same x-axis labels.
		$labels_by_key = array();
		$one_day       = new DateInterval( 'P1D' );
		$cursor        = $from_dt;
		while ( $cursor <= $to_dt ) {
			$date                   = $cursor->format( 'Y-m-d' );
			$key                    = $this->get_bucket_key( $date, $interval, $tz );
			$labels_by_key[ $key ]  = $this->get_bucket_label( $date, $interval, $tz );
			$cursor                 = $cursor->add( $one_day );
		}
		ksort( $labels_by_key );

		$empty_buckets = array_fill_keys( array_keys( $labels_by_key ), 0 );

		// Group rows by form_id, summing submissions into the appropriate bucket.
		$forms      = array();
		$total_subs = 0;
		$total_views = 0;
		foreach ( $rows as $row ) {
			$form_id     = isset( $row['form_id'] ) ? (string) $row['form_id'] : '';
			$date        = isset( $row['event_date'] ) ? (string) $row['event_date'] : '';
			$views       = isset( $row['views'] ) ? (int) $row['views'] : 0;
			$submissions = isset( $row['submissions'] ) ? (int) $row['submissions'] : 0;

			if ( '' === $date ) {
				continue;
			}

			$key = $this->get_bucket_key( $date, $interval, $tz );
			if ( ! array_key_exists( $key, $empty_buckets ) ) {
				continue;
			}

			if ( ! isset( $forms[ $form_id ] ) ) {
				$forms[ $form_id ] = array(
					'form_id'           => $form_id,
					'label'             => $this->get_form_label( $form_id ),
					'values'            => $empty_buckets,
					'total_submissions' => 0,
					'total_views'       => 0,
				);
			}

			$forms[ $form_id ]['values'][ $key ]    += $submissions;
			$forms[ $form_id ]['total_submissions'] += $submissions;
			$forms[ $form_id ]['total_views']       += $views;

			$total_subs  += $submissions;
			$total_views += $views;
		}

		// Convert each form's bucket map into an ordered numeric array
		$series = array();
		foreach ( $forms as $form ) {
			$series[] = array(
				'form_id'           => $form['form_id'],
				'label'             => $form['label'],
				'total_submissions' => $form['total_submissions'],
				'total_views'       => $form['total_views'],
				'values'            => array_values( $form['values'] ),
			);
		}

		usort(
			$series,
			function ( $a, $b ) {
				return $b['total_submissions'] <=> $a['total_submissions'];
			}
		);

		return array(
			'interval'    => $interval,
			'labels'      => array_values( $labels_by_key ),
			'series'      => $series,
			'total_subs'  => $total_subs,
			'total_views' => $total_views,
		);
	}

	/**
	 * Human-readable label for a form_id.
	 *
	 * Per-form identification is tracked via `form_id` in the analytics table
	 * but the current tracking layer (shortcode + block forms) writes empty
	 * IDs, so existing rows collapse into a single "All forms" series. Once
	 * per-form tracking lands, this method is the single place to resolve a
	 * form ID to its display label (block title / shortcode caption / etc.).
	 *
	 * @param string $form_id Form identifier.
	 * @return string
	 */
	private function get_form_label( string $form_id ): string {
		if ( '' === $form_id ) {
			return esc_html__( 'All forms', 'mailchimp' );
		}

		return sprintf(
			/* translators: %s: form identifier */
			esc_html__( 'Form %s', 'mailchimp' ),
			$form_id
		);
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
