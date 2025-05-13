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
 * @since x.x.x
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
	private $limit = 10;

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

	public function __construct() {
		require_once MCSF_DIR . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

		$this->user_sync = new Mailchimp_User_Sync();
	}

	/**
	 * Initialize the class.
	 */
	public function init() {
		add_action( $this->job_name, [ $this, 'run' ] );
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
			return;
		}

		$list_id = $this->get_list_id();
		$api     = $this->get_api();

		if ( ! $list_id || ! $api ) {
			$this->log( 'User sync process failed due to connection issues or list not selected.' );
			$this->user_sync->add_notice( __( 'We encountered a problem starting the user sync process due to connection issues or list not selected.', 'mailchimp' ), 'error' );
			return;
		}

		// Start the user sync job.
		$this->log( 'Started user sync job.' );

		$limit              = $this->get_limit();
		$processed          = $item['processed'] ? absint( $item['processed'] ) : 0;
		$offset             = $item['offset'] ? absint( $item['offset'] ) : 0;
		$user_sync_settings = $this->get_user_sync_settings();
		$user_roles         = $user_sync_settings['user_roles'] ?? array();

		// If no user roles to sync, add a notice and return.
		if ( empty( $user_roles ) ) {
			$this->log( 'No user roles to sync, please select at least one user role.' );
			$this->user_sync->add_notice( __( 'No user roles to sync, please select at least one user role.', 'mailchimp' ), 'warning' );
			return;
		}

		// Get users to sync.
		$users = get_users( array(
			'role__in' => $user_roles,
			'number'   => $limit,
			'offset'   => $offset,
			'fields'   => 'ID',
		) );

		// If no users to sync, add a notice and return.
		if ( empty( $users ) ) {
			$this->log( 'No users to sync.' );
			if ( $processed === 0 ) {
				$this->user_sync->add_notice( __( 'No users to sync.', 'mailchimp' ), 'warning' );
			} else {
				$this->user_sync->add_notice(
					sprintf(
						_n( 'User sync process completed. %d user processed.', 'User sync process completed. %d users processed.', $processed, 'mailchimp' ),
						$processed
					),
					'success'
				);
			}
			return;
		}

		// Sync users.
		foreach ( $users as $user ) {
			try{
				$this->sync_user( $user );
			} catch ( Exception $e ) {
				$this->log( 'Error getting user: ' . $e->getMessage() );
				continue;
			}
		}

		// If no more users to sync, add a notice and return.
		$found_users = count( $users );
		if ( $found_users < $limit ) {
			$this->log( 'No more users to sync, User sync process completed.' );
			$this->user_sync->add_notice(
				sprintf(
					_n( 'User sync process completed. %d user processed.', 'User sync process completed. %d users processed.', $processed, 'mailchimp' ),
					$processed
				),
				'success'
			);
			return;
		}

		// Schedule the next job batch.
		$item['processed'] += $found_users;
		$item['offset']     = $offset + $limit;
		$this->schedule( array( $item ) );
		return;
	}

	/**
	 * Sync the user.
	 *
	 * @param WP_User $user The user to sync.
	 */
	public function sync_user( $user_id ) {
		$list_id = $this->get_list_id();
		$api     = $this->get_api();
		$settings = $this->get_user_sync_settings();
		$existing_contacts_only = (bool) ($settings['existing_contacts_only'] ?? false);
		$subscribe_status       = $settings['subscriber_status'] ?? 'subscribed';

		$user = get_user_by( 'id', $user_id );

		if ( ! $user ) {
			$this->log( 'User not found' );
			return;
		}

		$this->log( 'Syncing user: ' . $user->ID );
		$user_email = strtolower( trim( $user->user_email ) );
		$user_first_name = $user->first_name;
		$user_last_name = $user->last_name;

		$merge_fields = array(
			'FNAME' => $user_first_name,
			'LNAME' => $user_last_name,
		);

		$merge_fields = apply_filters( 'mailchimp_sf_user_sync_merge_fields', $merge_fields, $user );

		$current_status = $this->get_mailchimp_user_status( $user_email );

		if ( $existing_contacts_only && ! $current_status ) {
			$this->log( 'User not exists on Mailchimp, skipping' );
			return;
		}

		$request_body = array(
			'email_address' => $user_email,
			'merge_fields' => $merge_fields
		);

		if ( $current_status ) {
			if ( $current_status === 'archived' ) {
				$request_body['status'] = $subscribe_status;
			} elseif ( $current_status === 'cleaned' ) {
				$request_body['status'] = 'pending';
			}
		} else {
			$request_body['status_if_new'] = $subscribe_status;
		}

		$endpoint = 'lists/' . $list_id . '/members/' . md5( $user_email ) . '?skip_merge_validation=true';
		$response = $api->post( $endpoint, $request_body, 'PUT', $list_id );

		if ( is_wp_error( $response ) ) {
			$this->log( 'Error syncing user: ' . $response->get_error_message() );
			return;
		}

		$this->log( 'User synced: ' . $user_email );
	}

	/**
	 * Schedule the user sync job.
	 *
	 * @param array $args Arguments to pass to the job.
	 */
	public function schedule( array $args = [] ) {
		if ( function_exists( 'as_enqueue_async_action' ) ) {
			as_enqueue_async_action( $this->job_name, $args );
		}
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

		$endpoint   = 'lists/' . $list_id . '/members/' . md5( $user_email ) . '?fields=status';

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
		error_log( 'Mailchimp User Sync: ' . $message );
	}
}
