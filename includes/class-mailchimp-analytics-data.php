<?php
/**
 * Class responsible for form analytics data storage and retrieval.
 *
 * @package Mailchimp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mailchimp_Analytics_Data
 */
class Mailchimp_Analytics_Data {

	/**
	 * Database version for the analytics table.
	 *
	 * @var string
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Initialize the class.
	 */
	public function init() {
		add_action( 'wp_ajax_mailchimp_sf_track_form_view', array( $this, 'handle_form_view' ) );
		add_action( 'wp_ajax_nopriv_mailchimp_sf_track_form_view', array( $this, 'handle_form_view' ) );
		add_action( 'wp_ajax_mailchimp_sf_get_analytics', array( $this, 'handle_get_analytics' ) );
		add_action( 'mailchimp_sf_form_submission_success', array( $this, 'track_submission' ) );
	}

	/**
	 * Get the analytics table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'mailchimp_sf_form_analytics';
	}

	/**
	 * Create the analytics table.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			list_id varchar(20) NOT NULL,
			form_id varchar(50) NOT NULL DEFAULT '',
			event_date date NOT NULL,
			views bigint(20) unsigned NOT NULL DEFAULT 0,
			submissions bigint(20) unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			UNIQUE KEY list_form_date (list_id, form_id, event_date)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'mailchimp_sf_analytics_db_version', self::DB_VERSION );
	}

	/**
	 * Increment the view count for a list on today's date.
	 *
	 * @param string $list_id The list ID.
	 * @param string $form_id The form ID.
	 */
	public function increment_views( $list_id, $form_id = '' ) {
		global $wpdb;

		$table_name = self::get_table_name();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table_name} (list_id, form_id, event_date, views, submissions)
				VALUES (%s, %s, %s, 1, 0)
				ON DUPLICATE KEY UPDATE views = views + 1",
				$list_id,
				$form_id,
				current_time( 'Y-m-d' )
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( false === $result && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Mailchimp Analytics: Failed to increment views for list_id ' . sanitize_text_field( $list_id ) . '. DB error: ' . $wpdb->last_error );
		}
	}

	/**
	 * Increment the submission count for a list on today's date.
	 *
	 * @param string $list_id The list ID.
	 * @param string $form_id The form ID.
	 */
	public function increment_submissions( $list_id, $form_id = '' ) {
		global $wpdb;

		$table_name = self::get_table_name();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table_name} (list_id, form_id, event_date, views, submissions)
				VALUES (%s, %s, %s, 0, 1)
				ON DUPLICATE KEY UPDATE submissions = submissions + 1",
				$list_id,
				$form_id,
				current_time( 'Y-m-d' )
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( false === $result && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Mailchimp Analytics: Failed to increment submissions for list_id ' . sanitize_text_field( $list_id ) . '. DB error: ' . $wpdb->last_error );
		}
	}

	/**
	 * Get analytics data for a list within a date range.
	 *
	 * @param string $list_id    The list ID.
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @return array Array of daily analytics rows.
	 */
	public function get_analytics_data( $list_id, $start_date, $end_date ) {
		global $wpdb;

		$table_name = self::get_table_name();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT event_date, SUM(views) AS views, SUM(submissions) AS submissions
				FROM {$table_name}
				WHERE list_id = %s AND event_date BETWEEN %s AND %s
				GROUP BY event_date
				ORDER BY event_date ASC",
				$list_id,
				$start_date,
				$end_date
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $results;
	}

	/**
	 * Get totals for a list within a date range.
	 *
	 * @param string $list_id    The list ID.
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @return array Associative array with total views and submissions.
	 */
	public function get_totals( $list_id, $start_date, $end_date ) {
		global $wpdb;

		$table_name = self::get_table_name();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(views), 0) AS total_views, COALESCE(SUM(submissions), 0) AS total_submissions
				FROM {$table_name}
				WHERE list_id = %s AND event_date BETWEEN %s AND %s",
				$list_id,
				$start_date,
				$end_date
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( ! $result ) {
			return array(
				'total_views'       => 0,
				'total_submissions' => 0,
			);
		}

		return $result;
	}

	/**
	 * Handle the AJAX form view tracking request.
	 */
	public function handle_form_view() {
		// Verify nonce.
		if ( ! isset( $_POST['mailchimp_sf_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mailchimp_sf_nonce'] ), 'mailchimp_sf_analytics_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce.', 403 );
		}

		$list_id = isset( $_POST['list_id'] ) ? sanitize_text_field( wp_unslash( $_POST['list_id'] ) ) : '';

		if ( empty( $list_id ) ) {
			wp_send_json_error( 'Missing list_id.', 400 );
		}

		// Ensure the list ID is one of the configured/known lists to prevent
		// arbitrary IDs from polluting analytics data.
		if ( ! $this->is_valid_list_id( $list_id ) ) {
			wp_send_json_error( 'Invalid list_id.', 400 );
		}

		$this->increment_views( $list_id );
		wp_send_json_success();
	}

	/**
	 * Determine whether a list ID is one of the configured/known lists.
	 *
	 * This helps ensure that analytics data is only recorded for legitimate lists
	 * configured within the plugin options.
	 *
	 * @param string $list_id The list ID to validate.
	 * @return bool True if the list ID is known/configured, false otherwise.
	 */
	private function is_valid_list_id( $list_id ) {
		if ( empty( $list_id ) ) {
			return false;
		}

		$valid_ids = array();

		// Collect list IDs from the stored Mailchimp lists option, if present.
		$mailchimp_lists = get_option( 'mailchimp_sf_lists' );
		if ( is_array( $mailchimp_lists ) ) {
			foreach ( $mailchimp_lists as $list ) {
				// Handle both scalar IDs and associative array structures.
				if ( is_string( $list ) || is_int( $list ) ) {
					$valid_ids[] = (string) $list;
				} elseif ( is_array( $list ) ) {
					// Common keys used to store list IDs.
					foreach ( array( 'id', 'list_id', 'mc_list_id' ) as $key ) {
						if ( isset( $list[ $key ] ) && ! empty( $list[ $key ] ) ) {
							$valid_ids[] = (string) $list[ $key ];
						}
					}
				}
			}
		}

		// Include the active Mailchimp list ID option, if set.
		$active_list_id = get_option( 'mc_list_id' );
		if ( ! empty( $active_list_id ) ) {
			$valid_ids[] = (string) $active_list_id;
		}

		// If we have no configured IDs, fail closed and do not treat arbitrary IDs as valid.
		if ( empty( $valid_ids ) ) {
			return false;
		}

		$valid_ids = array_unique( $valid_ids );

		return in_array( (string) $list_id, $valid_ids, true );
	}

	/**
	 * Handle the AJAX request to fetch analytics data.
	 */
	public function handle_get_analytics() {
		if ( ! current_user_can( MCSF_CAP_THRESHOLD ) ) {
			wp_send_json_error( 'Unauthorized.', 403 );
		}

		check_ajax_referer( 'mailchimp_sf_analytics_admin_nonce', 'nonce' );

		$list_id    = isset( $_POST['list_id'] ) ? sanitize_text_field( wp_unslash( $_POST['list_id'] ) ) : '';
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';

		if ( empty( $list_id ) || empty( $start_date ) || empty( $end_date ) ) {
			wp_send_json_error( 'Missing required parameters.', 400 );
		}

		$totals = $this->get_totals( $list_id, $start_date, $end_date );
		$daily  = $this->get_analytics_data( $list_id, $start_date, $end_date );

		wp_send_json_success(
			array(
				'totals' => $totals,
				'daily'  => $daily,
			)
		);
	}

	/**
	 * Track a successful form submission.
	 *
	 * @param string $list_id The list ID.
	 */
	public function track_submission( $list_id ) {
		if ( ! empty( $list_id ) ) {
			$this->increment_submissions( $list_id );
		}
	}
}
