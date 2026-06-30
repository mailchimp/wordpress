<?php
/**
 * Registry of block-based signup forms.
 *
 * Stores a stable `form_id` to human-readable title mapping so the analytics
 * dashboard can label per-form data. Rows are upserted when a host post is
 * saved and are never deleted, so historical labels keep resolving.
 *
 * @package Mailchimp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mailchimp_Forms_Registry
 */
class Mailchimp_Forms_Registry {

	/**
	 * Database version for the forms registry table.
	 *
	 * @var string
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Initialize the class.
	 */
	public function init() {
		add_action( 'save_post', array( $this, 'sync_post_forms' ), 10, 2 );
	}

	/**
	 * Get the forms registry table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'mailchimp_sf_forms';
	}

	/**
	 * Create the forms registry table.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			form_id varchar(50) NOT NULL,
			title varchar(255) NOT NULL DEFAULT '',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (form_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'mailchimp_sf_forms_db_version', self::DB_VERSION );
	}

	/**
	 * Validate and normalize a form ID.
	 *
	 * Accepts a UUID (the value minted by the block editor) and returns it
	 * lower-cased. Anything else, including an empty value, returns ''
	 *
	 * @param mixed $form_id The candidate form ID.
	 * @return string The valid UUID, or '' if not valid.
	 */
	public static function sanitize_form_id( $form_id ) {
		if ( ! is_string( $form_id ) ) {
			return '';
		}

		$form_id = strtolower( trim( $form_id ) );

		if ( '' === $form_id ) {
			return '';
		}

		return preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $form_id ) ? $form_id : '';
	}

	/**
	 * Sync the registry with the Mailchimp forms found in a saved post.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object.
	 */
	public function sync_post_forms( $post_id, $post ) {
		// Skip autosaves and revisions.
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Only parse posts that actually contain the block.
		if ( ! $post instanceof WP_Post || ! has_block( 'mailchimp/mailchimp', $post ) ) {
			return;
		}

		$forms = $this->extract_forms( parse_blocks( $post->post_content ) );

		foreach ( $forms as $form_id => $title ) {
			$this->upsert( $form_id, $title );
		}
	}

	/**
	 * Recursively extract Mailchimp form IDs and titles from parsed blocks.
	 *
	 * @param array $blocks Parsed blocks.
	 * @param array $forms  Accumulator of `form_id => title`.
	 * @return array
	 */
	private function extract_forms( $blocks, $forms = array() ) {
		foreach ( $blocks as $block ) {
			if ( 'mailchimp/mailchimp' === ( $block['blockName'] ?? '' ) ) {
				$attrs   = $block['attrs'] ?? array();
				$form_id = self::sanitize_form_id( $attrs['formId'] ?? '' );

				if ( '' !== $form_id ) {
					// Fall back to the form header when no title is set.
					$title             = $attrs['formTitle'] ?? '';
					$title             = '' !== $title ? $title : ( $attrs['header'] ?? '' );
					$forms[ $form_id ] = sanitize_text_field( $title );
				}
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$forms = $this->extract_forms( $block['innerBlocks'], $forms );
			}
		}

		return $forms;
	}

	/**
	 * Insert or update a registry row.
	 *
	 * @param string $form_id The form ID (already validated).
	 * @param string $title   The form title.
	 */
	public function upsert( $form_id, $title ) {
		global $wpdb;

		$table_name = self::get_table_name();
		$now        = current_time( 'mysql' );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table_name} (form_id, title, created_at, updated_at)
				VALUES (%s, %s, %s, %s)
				ON DUPLICATE KEY UPDATE title = VALUES(title), updated_at = VALUES(updated_at)",
				$form_id,
				$title,
				$now,
				$now
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
}
