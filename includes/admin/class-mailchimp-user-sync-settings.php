<?php
/**
 * Class responsible for Mailchimp User Sync Settings.
 *
 * @package Mailchimp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mailchimp_User_Sync_Settings
 *
 * @since x.x.x
 */
class Mailchimp_User_Sync_Settings {

	/**
	 * The option name.
	 *
	 * @since x.x.x
	 * @var string
	 */
	protected $option_name = 'mailchimp_sf_user_sync_settings';

	/**
	 * Initialize the class
	 */
	public function init() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', [ $this, 'setup_fields_sections' ] );
	}

	/**
	 * Register the user sync settings.
	 *
	 * @since x.x.x
	 */
	public function register_settings() {
		$args = array(
			'sanitize_callback' => array( $this, 'sanitize_user_sync_settings' ),
		);

		register_setting( $this->option_name, $this->option_name, $args );
	}

	/**
	 * Setup the fields and sections.
	 *
	 * @since x.x.x
	 */
	public function setup_fields_sections() {
		$section_id = $this->option_name . '_section';
		add_settings_section(
			$section_id,
			'',
			'__return_empty_string',
			$this->option_name
		);

		add_settings_field(
			'user_sync_title',
			__( 'User Sync settings', 'mailchimp' ),
			'__return_empty_string',
			$this->option_name,
			$section_id
		);

		add_settings_field(
			'enable_user_sync',
			__( 'Enable Auto User Sync', 'mailchimp' ),
			array( $this, 'enable_user_sync_field' ),
			$this->option_name,
			$section_id,
			[
				'class' => 'mailchimp-user-sync-enable-user-sync',
			]
		);

		add_settings_field(
			'existing_contacts_only',
			__( 'Sync existing contacts only', 'mailchimp' ),
			array( $this, 'existing_contacts_only_field' ),
			$this->option_name,
			$section_id,
			[
				'class' => 'mailchimp-user-sync-existing-contacts-only',
			]
		);

		add_settings_field(
			'subscriber_status',
			__( 'Subscriber Status', 'mailchimp' ),
			array( $this, 'subscriber_status_field' ),
			$this->option_name,
			$section_id,
			[
				'class' => 'mailchimp-user-sync-subscriber-status',
			]
		);

		add_settings_field(
			'user_roles',
			__( 'Roles to sync', 'mailchimp' ),
			array( $this, 'user_roles_field' ),
			$this->option_name,
			$section_id,
			[
				'class' => 'mailchimp-user-sync-user-roles',
			]
		);

		add_settings_field(
			'sync_all_users',
			__( 'Sync users', 'mailchimp' ),
			array( $this, 'sync_all_users_button' ),
			$this->option_name,
			$section_id
		);
	}

	/**
	 * Get the user sync settings.
	 *
	 * @since x.x.x
	 * @return array The user sync settings.
	 */
	public function get_user_sync_settings( $key = null ) {
		$default_settings = array(
			'enable_user_sync' => 0,
			'user_roles'       => array(
				'subscriber' => 'subscriber'
			),
			'existing_contacts_only' => 0,
			'subscriber_status' => 'pending'
		);

		$settings = get_option( $this->option_name, array() );
		$settings = wp_parse_args( $settings, $default_settings );

		if ( $key ) {
			return $settings[ $key ] ?? null;
		}

		return $settings;
	}

	/**
	 * Sanitize the user sync settings.
	 *
	 * @since x.x.x
	 * @param array $settings The settings to sanitize.
	 * @return array The sanitized settings.
	 */
	public function sanitize_user_sync_settings( $new_settings ) {
		$settings                           = $this->get_user_sync_settings();
		$settings['enable_user_sync']       = isset( $new_settings['enable_user_sync'] ) ? 1 : 0;
		$settings['user_roles']             = isset( $new_settings['user_roles'] ) ? array_map( 'sanitize_text_field', $new_settings['user_roles'] ) : array();
		$settings['existing_contacts_only'] = isset( $new_settings['existing_contacts_only'] ) ? 1 : 0;
		$settings['subscriber_status']      = isset( $new_settings['subscriber_status'] ) ? sanitize_text_field( $new_settings['subscriber_status'] ) : 'pending';

		return $settings;
	}

	/**
	 * Render the user roles field.
	 *
	 * @since x.x.x
	 */
	public function user_roles_field() {
		$settings   = $this->get_user_sync_settings( 'user_roles' );
		$user_roles = get_editable_roles();

		foreach ( $user_roles as $role_name => $role_details ) {
			$value = $settings[ $role_name ] ?? '';

			// Render checkbox.
			printf(
				'<p>
					<label for="user_roles_%1$s">
						<input type="checkbox" id="user_roles_%1$s" name="%1$s" value="%2$s" %3$s />
						%4$s
					</label>
				</p>',
				esc_attr( $this->option_name . '[user_roles][' . $role_name . ']' ),
				esc_attr( $role_name ),
				checked( $value, $role_name, false ),
				esc_html( $role_details['name'] )
			);
		}
		?>
		<p class="description">
			<?php esc_html_e( 'Select the roles that should be synced to Mailchimp.', 'mailchimp' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the enable user sync field.
	 *
	 * @since x.x.x
	 */
	public function enable_user_sync_field( $args ) {
		$value = $this->get_user_sync_settings( 'enable_user_sync' );
		?>
		<input
			type="checkbox"
			name="<?php echo esc_attr( $this->option_name . '[enable_user_sync]' ); ?>"
			id="enable_user_sync"
			value="1"
			<?php checked( absint( $value ), 1, true ); ?>
		>
		<p class="description">
			<?php esc_html_e( 'Automatically sync users to Mailchimp when they are created or updated.', 'mailchimp' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the subscriber status field.
	 *
	 * @since x.x.x
	 */
	public function subscriber_status_field() {
		$settings = $this->get_user_sync_settings( 'subscriber_status' );
		?>
		<div>
			<label for="subscriber_status_subscribed" class="subscribe_status_label">
				<input type="radio" id="subscriber_status_subscribed" name="<?php echo esc_attr( $this->option_name . '[subscriber_status]' ); ?>" value="subscribed" <?php checked( $settings, 'subscribed' ); ?> />
				<?php esc_html_e( 'Sync as Subscribed', 'mailchimp' ); ?>
			</label>
			<p class="description_small">
				<?php esc_html_e( 'This status indicates that you\'ve gotten permission to market to your users.', 'mailchimp' ); ?>
				<a href="https://mailchimp.com/help/the-importance-of-permission/" target="_blank"><?php esc_html_e( 'Learn more about the importance of permission.', 'mailchimp' ); ?></a>
			</p>
		</div>
		<div>
			<label for="subscriber_status_pending" class="subscribe_status_label">
				<input type="radio" id="subscriber_status_pending" name="<?php echo esc_attr( $this->option_name . '[subscriber_status]' ); ?>" value="pending" <?php checked( $settings, 'pending' ); ?> />
				<?php esc_html_e( 'Sync as Pending', 'mailchimp' ); ?>
			</label>
			<p class="description_small">
				<?php esc_html_e( 'This status indicates that a double opt-in email will be sent to users to confirm their subscription.', 'mailchimp' ); ?>
			</p>
		</div>
		<div>
			<label for="subscriber_status_non_subscribed" class="subscribe_status_label">
				<input type="radio" id="subscriber_status_non_subscribed" name="<?php echo esc_attr( $this->option_name . '[subscriber_status]' ); ?>" value="non_subscribed" <?php checked( $settings, 'non_subscribed' ); ?> />
				<?php esc_html_e( 'Sync as non-subscribed', 'mailchimp' ); ?>
			</label>
			<p class="description_small">
				<?php esc_html_e( 'This status indicates you haven\'t gotten permission to market to these users. However, you can use Mailchimp to send ', 'mailchimp' ); ?><a href="https://mailchimp.com/help/about-non-subscribed-contacts/" target="_blank"><?php esc_html_e( 'non-subscribed contacts', 'mailchimp' ); ?></a> <?php esc_html_e( 'transactional emails and postcards and target them with ads.', 'mailchimp' ); ?>
			</p>
		</div>
		<p class="description_small">
			<?php esc_html_e( 'You will need', 'mailchimp' ); ?> <a href="https://mailchimp.com/help/about-mailchimp-pricing-plans/" target="_blank"><?php esc_html_e( 'a Mailchimp plan', 'mailchimp' ); ?></a> <?php esc_html_e( 'that includes 1 contacts. If your plan does not include enough contacts, you will incur additional monthly charges.', 'mailchimp' ); ?> <a href="https://mailchimp.com/help/about-additional-charges/" target="_blank"><?php esc_html_e( 'Learn about additional charges.', 'mailchimp' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Render the existing contacts only field.
	 *
	 * @since x.x.x
	 */
	public function existing_contacts_only_field() {
		$settings = $this->get_user_sync_settings();
		$existing_contacts_only = isset( $settings['existing_contacts_only'] ) ? $settings['existing_contacts_only'] : 0;
		?>
		<input type="checkbox" name="<?php echo esc_attr( $this->option_name . '[existing_contacts_only]' ); ?>" value="1" <?php checked( $existing_contacts_only, 1, true ); ?> />
		<p class="description">
			<?php esc_html_e( 'Only WordPress users who are already in your Mailchimp audience will sync. You won’t be able to send your other users postcards or target them with ads.', 'mailchimp' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the sync all users field.
	 *
	 * @since x.x.x
	 */
	public function sync_all_users_button() {
		?>
		<button class="button button-secondary" id="sync_all_users">
			<?php esc_html_e( 'Sync all users', 'mailchimp' ); ?>
		</button>
		<p class="description">
			<?php esc_html_e( 'Sync all users to Mailchimp.', 'mailchimp' ); ?>
		</p>
		<?php
	}
}
