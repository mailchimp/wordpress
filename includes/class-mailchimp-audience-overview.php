<?php
/**
 * Audience Overview KPI block data provider for the Analytics page.
 *
 * @package Mailchimp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mailchimp_Audience_Overview
 */
class Mailchimp_Audience_Overview {

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
		add_action( 'wp_ajax_mailchimp_sf_get_audience_overview', array( $this, 'handle_get' ) );
	}

	/**
	 * AJAX handler for `mailchimp_sf_get_audience_overview`.
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

		$analytics_data    = new Mailchimp_Analytics_Data();
		$totals            = $analytics_data->get_totals( $list_id, $date_from, $date_to );
		$total_views       = isset( $totals['total_views'] ) ? (int) $totals['total_views'] : 0;
		$total_submissions = isset( $totals['total_submissions'] ) ? (int) $totals['total_submissions'] : 0;

		wp_send_json_success(
			array(
				'total_subscribers'     => $this->fetch_total_subscribers( $list_id ),
				'total_views'           => $total_views,
				'total_submissions'     => $total_submissions,
				'total_conversion_rate' => $this->conversion_rate( $total_submissions, $total_views ),
			)
		);
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
}
