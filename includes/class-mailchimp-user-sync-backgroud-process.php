<?php
/**
 * Class responsible for Mailchimp User Sync Background Process.
 *
 * @package Mailchimp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mailchimp_User_Sync_Background_Process
 *
 * @since 1.9.0
 */
class Mailchimp_User_Sync_Background_Process {

	/**
	 * The name of the job.
	 *
	 * @var string
	 */
	private $job_name = 'mailchimp_sf_user_sync_background_process';

	/**
	 * The limit of users to sync.
	 *
	 * @var int
	 */
	private $limit = 20;

	/**
	 * The API instance.
	 *
	 * @var object
	 */
	private $api;

	/**
	 * The user sync object.
	 *
	 * @var Mailchimp_User_Sync
	 */
	private $user_sync;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->user_sync = new Mailchimp_User_Sync();
		$manual_sync     = get_option( 'mailchimp_sf_user_sync_running', false );
		$settings        = $this->user_sync->get_user_sync_settings();

		// Load the Action Scheduler library, if auto user sync is enabled or manual sync is running.
		if ( $manual_sync || ( isset( $settings['enable_user_sync'] ) && 1 === absint( $settings['enable_user_sync'] ) ) ) {
			require_once MCSF_DIR . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
		}
	}

	/**
	 * Initialize the class.
	 */
	public function init() {
		add_action( $this->job_name, [ $this, 'run' ] );
		add_action( 'mailchimp_sf_handle_user_update', [ $this, 'handle_user_update' ] );
	}

	/**
	 * Run the user sync job.
	 *
	 * @param array $item Item details to process.
	 */
	public function run( $item = array() ) {
		// Check if cancel request is made.
		if ( isset( $item['job_id'] ) && get_transient( 'mailchimp_sf_cancel_user_sync_process' ) === $item['job_id'] ) {
			delete_transient( 'mailchimp_sf_cancel_user_sync_process' );
			$this->clear_running_sync();
			return;
		}

		$list_id = $this->get_list_id();
		$api     = $this->get_api();

		if ( ! $list_id || ! $api || ! $item['list_id'] || $item['list_id'] !== $list_id ) {
			$this->log( 'User sync process failed due to connection issues or list not selected.' );
			$this->user_sync->add_notice( __( 'We encountered a problem starting the user sync process due to connection issues or list not selected.', 'mailchimp' ), 'error' );
			$this->clear_running_sync();
			return;
		}

		// Start the user sync job.
		$this->log( 'Started user sync job.' );

		$limit              = $this->get_limit();
		$processed          = $item['processed'] ? absint( $item['processed'] ) : 0;
		$offset             = $item['offset'] ? absint( $item['offset'] ) : 0;
		$user_sync_settings = $this->get_user_sync_settings();
		$user_roles         = $user_sync_settings['user_roles'] ?? array();
		$errors             = array();

		// If no user roles to sync, add a notice and return.
		if ( empty( $user_roles ) ) {
			$this->log( 'No user roles to sync, please select at least one user role.' );
			$this->user_sync->add_notice( __( 'No user roles to sync, please select at least one user role.', 'mailchimp' ), 'warning' );
			$this->clear_running_sync();
			return;
		}

		// Get users to sync.
		$users = get_users(
			array(
				'role__in' => $user_roles,
				'number'   => $limit,
				'offset'   => $offset,
				'fields'   => 'ID',
			)
		);

		// If no users to sync, add a notice and return.
		if ( empty( $users ) ) {
			$this->log( 'No users to sync.' );
			if ( 0 === $processed ) {
				$this->user_sync->add_notice( __( 'No users to sync.', 'mailchimp' ), 'warning' );
			} else {
				$this->user_sync->add_notice(
					sprintf(
						/* translators: %d: number of processed users. */
						_n( 'User sync process completed. %d user processed.', 'User sync process completed. %d users processed.', $processed, 'mailchimp' ),
						$processed
					),
					'success'
				);
			}
			$this->clear_running_sync();
			return;
		}

		// Sync users.
		foreach ( $users as $user_id ) {
			try {
				$user = get_user_by( 'id', $user_id );
				if ( ! $user ) {
					$this->log( 'User not found' );
					$item['skipped'] += 1;
					continue;
				}

				$synced = $this->sync_user( $user );
				if ( is_wp_error( $synced ) ) {
					$item['failed']                           += 1;
					$errors[ uniqid( 'mailchimp_sf_error_' ) ] = array(
						'time'    => time(),
						'user_id' => $user->ID,
						'email'   => $user->user_email,
						'error'   => $synced->get_error_message(),
					);
				} elseif ( $synced ) {
					$item['success'] += 1;
				} else {
					$item['skipped'] += 1;
				}
			} catch ( Exception $e ) {
				$this->log( 'Error getting user: ' . $e->getMessage() );
				$item['failed']                           += 1;
				$errors[ uniqid( 'mailchimp_sf_error_' ) ] = array(
					'time'    => time(),
					'user_id' => $user->ID,
					'email'   => $user->user_email,
					'error'   => $e->getMessage(),
				);
				continue;
			}
		}

		// Save errors.
		$this->user_sync->set_user_sync_errors( $errors );

		// If no more users to sync, add a notice and return.
		$found_users = count( $users );
		if ( $found_users < $limit ) {
			$processed += $found_users;
			$this->log( 'No more users to sync, User sync process completed. ' . absint( $processed ) . ' users processed.' );
			$this->user_sync->add_notice(
				sprintf(
					/* translators: %1$d: number of processed users, %2$d: number of synced users, %3$d: number of failed users, %4$d: number of skipped users. */
					_n( 'User sync process completed. %1$d user processed (Synced: %2$d, Failed: %3$d, Skipped: %4$d).', 'User sync process completed. %1$d users processed (Synced: %2$d, Failed: %3$d, Skipped: %4$d).', absint( $processed ), 'mailchimp' ),
					absint( $processed ),
					absint( $item['success'] ),
					absint( $item['failed'] ),
					absint( $item['skipped'] )
				),
				'success'
			);
			$this->clear_running_sync();
			return;
		}

		// Schedule the next job batch.
		$item['processed'] += $found_users;
		$item['offset']     = $offset + $limit;
		$this->schedule( array( $item ) );
	}

	/**
	 * Handle the user update.
	 *
	 * @param int $user_id The user ID.
	 */
	public function handle_user_update( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			$this->log( 'User not found' );
			return;
		}

		$errors = array();
		$synced = $this->sync_user( $user );
		if ( is_wp_error( $synced ) ) {
			$errors[ uniqid( 'mailchimp_sf_error_' ) ] = array(
				'time'    => time(),
				'user_id' => $user->ID,
				'email'   => $user->user_email,
				'error'   => $synced->get_error_message(),
			);
			$this->user_sync->set_user_sync_errors( $errors );
		}
	}

	/**
	 * Sync the user.
	 *
	 * @param WP_User $user The user.
	 * @return bool|WP_Error True if the user is synced, WP_Error if there is an error and false if the user is not found or not synced.
	 */
	public function sync_user( $user ) {
		$list_id                = $this->get_list_id();
		$api                    = $this->get_api();
		$settings               = $this->get_user_sync_settings();
		$existing_contacts_only = (bool) ( $settings['existing_contacts_only'] ?? false );
		$subscribe_status       = $settings['subscriber_status'] ?? 'pending';

		$this->log( 'Syncing user: ' . $user->user_email . ' (ID: ' . $user->ID . ')' );
		$user_email = strtolower( trim( $user->user_email ) );

		// Check if user exists on Mailchimp.
		$current_status = $this->get_mailchimp_user_status( $user_email );

		if ( $existing_contacts_only && ! $current_status ) {
			$this->log( 'User not exists on Mailchimp, skipping' );
			return false;
		}

		$request_body = array(
			'email_address' => $user_email,
			'status'        => $this->get_subscribe_status( $subscribe_status, $current_status, $user ),
		);
		$merge_fields = $this->get_user_merge_fields( $user );
		if ( ! empty( $merge_fields ) ) {
			$request_body['merge_fields'] = $merge_fields;
		}

		$this->log( 'Request body: ' . wp_json_encode( $request_body ) );

		$endpoint = 'lists/' . $list_id . '/members/' . md5( $user_email ) . '?skip_merge_validation=true';
		$response = $api->post( $endpoint, $request_body, 'PUT', $list_id, true );

		if ( is_wp_error( $response ) ) {
			$this->log( 'Error syncing user: ' . $response->get_error_message() );
			return $response;
		}

		$this->log( 'User synced: ' . $user_email );
		return true;
	}

	/**
	 * Get the subscribe status.
	 *
	 * @param string  $subscribe_status The subscribe status.
	 * @param string  $current_status The current status.
	 * @param WP_User $user The user.
	 * @return string
	 */
	public function get_subscribe_status( $subscribe_status, $current_status, $user ) {
		if ( $current_status ) {
			switch ( $current_status ) {
				// If user is already subscribed, unsubscribed or transactional, don't change the status.
				case 'subscribed':
				case 'unsubscribed':
				case 'transactional':
					$subscribe_status = $current_status;
					break;

				// If user is cleaned, set the status as pending.
				case 'cleaned':
					$subscribe_status = 'pending';
					break;

				// If user is archived, pending or anything else, set the status as per the subscribe status in settings.
				case 'archived':
				case 'pending':
				default:
					break;
			}
		}

		// If the subscribe status is not set (sync existing contacts only), set it to the current status.
		if ( ! $subscribe_status && $current_status ) {
			$subscribe_status = $current_status;
		}

		/**
		 * Filter the subscribe status.
		 *
		 * @param string $subscribe_status The subscribe status set in settings.
		 * @param string $current_status The current subscribe status of the user on Mailchimp.
		 * @param WP_User $user The user.
		 * @return string
		 */
		return apply_filters( 'mailchimp_sf_user_sync_subscribe_status', $subscribe_status, $current_status, $user );
	}

	/**
	 * Get the user merge fields.
	 *
	 * @param WP_User $user The user to get the merge fields for.
	 * @return array
	 */
	public function get_user_merge_fields( $user ) {
		$merge_fields = array();

		if ( ! empty( $user->first_name ) ) {
			$merge_fields['FNAME'] = $user->first_name;
		}

		if ( ! empty( $user->last_name ) ) {
			$merge_fields['LNAME'] = $user->last_name;
		}

		/**
		 * Filter the user merge fields.
		 *
		 * @param array $merge_fields The merge fields.
		 * @param WP_User $user The user.
		 * @return array
		 */
		return apply_filters( 'mailchimp_sf_user_sync_merge_fields', $merge_fields, $user );
	}

	/**
	 * Schedule the user sync job.
	 *
	 * @param array $args Arguments to pass to the job.
	 */
	public function schedule( array $args = [] ) {
		if ( function_exists( 'as_enqueue_async_action' ) ) {
			as_enqueue_async_action( $this->job_name, $args );
			$this->mark_sync_running();
		}
	}

	/**
	 * Mark the sync as running.
	 */
	public function mark_sync_running() {
		update_option( 'mailchimp_sf_user_sync_running', true );
	}

	/**
	 * Clear the running sync option.
	 */
	public function clear_running_sync() {
		delete_option( 'mailchimp_sf_user_sync_running' );
	}

	/**
	 * Unschedule the user sync job.
	 *
	 * @return bool
	 */
	public function unschedule() {
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( $this->job_name );

			if ( ! class_exists( 'ActionScheduler_Store' ) ) {
				return false;
			}

			$store = ActionScheduler_Store::instance();

			// Check if the job is still in progress.
			$action_id = $store->find_action(
				$this->job_name,
				array(
					'status' => ActionScheduler_Store::STATUS_RUNNING,
				)
			);

			// If no action running, return true.
			if ( empty( $action_id ) ) {
				return true;
			}

			$action = $store->fetch_action( $action_id );
			$args   = $action->get_args();
			if ( ! empty( $args ) && isset( $args[0]['job_id'] ) ) {
				set_transient( 'mailchimp_sf_cancel_user_sync_process', $args[0]['job_id'], 300 );
			}

			return true;
		}

		return false;
	}

	/**
	 * Check if job is in progress.
	 *
	 * @return bool
	 */
	public function in_progress(): bool {
		if ( function_exists( 'as_has_scheduled_action' ) ) {
			return as_has_scheduled_action( $this->job_name );
		}

		return false;
	}

	/**
	 * Get the arguments for the current job.
	 *
	 * @return array|bool
	 */
	public function get_args() {
		if ( ! class_exists( 'ActionScheduler_Store' ) ) {
			return false;
		}

		$store = ActionScheduler_Store::instance();

		$running_action_id = $store->find_action(
			$this->job_name,
			array(
				'status' => ActionScheduler_Store::STATUS_RUNNING,
			)
		);

		$pending_action_id = $store->find_action(
			$this->job_name,
			array(
				'status' => ActionScheduler_Store::STATUS_PENDING,
			)
		);

		if ( empty( $running_action_id ) && empty( $pending_action_id ) ) {
			return false;
		}

		$action_id = ! empty( $running_action_id ) ? $running_action_id : $pending_action_id;
		$action    = $store->fetch_action( $action_id );
		$args      = $action->get_args();

		return $args;
	}

	/**
	 * Get the limit of users to sync.
	 *
	 * @return int
	 */
	public function get_limit() {
		/**
		 * Filter the limit of users to sync.
		 *
		 * @param int $limit The limit of users to sync.
		 * @return int
		 */
		return apply_filters( 'mailchimp_sf_user_sync_limit', $this->limit );
	}

	/**
	 * Get the user sync settings.
	 *
	 * @return array
	 */
	public function get_user_sync_settings() {
		$user_sync = new Mailchimp_User_Sync();
		return $user_sync->get_user_sync_settings();
	}

	/**
	 * Get the API instance.
	 *
	 * @return object
	 */
	public function get_api() {
		if ( ! $this->api ) {
			$this->api = mailchimp_sf_get_api();
		}

		return $this->api;
	}

	/**
	 * Get the list ID.
	 *
	 * @return string
	 */
	public function get_list_id() {
		return get_option( 'mc_list_id' );
	}

	/**
	 * Get the mailchimp user status.
	 *
	 * @param string $user_email The user email.
	 * @return string
	 */
	public function get_mailchimp_user_status( $user_email ) {
		$list_id    = $this->get_list_id();
		$user_email = strtolower( trim( $user_email ) );
		$api        = $this->get_api();

		$endpoint = 'lists/' . $list_id . '/members/' . md5( $user_email ) . '?fields=status';

		$subscriber = $api->get( $endpoint, null );
		if ( is_wp_error( $subscriber ) ) {
			return false;
		}
		return $subscriber['status'];
	}

	/**
	 * Log a message.
	 *
	 * @param string $message The message to log.
	 */
	public function log( $message ) {
		$should_log = apply_filters( 'mailchimp_sf_user_sync_log', false );
		if ( $should_log ) {
			error_log( 'Mailchimp User Sync: ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
