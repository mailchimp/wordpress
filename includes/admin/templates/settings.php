<?php
/**
 * Main settings page template
 *
 * @package Mailchimp
 */

use function Mailchimp\WordPress\Includes\Admin\admin_notice_error;

$user         = get_option( 'mc_user' );
$is_logged_in = ! ( ! $user || ( ! get_option( 'mc_api_key' ) && ! mailchimp_sf_get_access_token() ) );

// If we have an API Key, see if we need to change the lists and its options
mailchimp_sf_change_list_if_necessary();

$is_list_selected = false;
?>
<div id="mailchimp-sf-settings-page">
	<?php
	// Header.
	include_once MCSF_DIR . 'includes/admin/templates/header.php'; // phpcs:ignore PEAR.Files.IncludingFile.UseRequireOnce

	// If user is not logged in, show login form.
	if ( ! $is_logged_in ) {
		include_once MCSF_DIR . 'includes/admin/templates/login.php';
	} else {
		$user = get_option( 'mc_user' );

		// Settings header.
		include_once MCSF_DIR . 'includes/admin/templates/settings-header.php';
		?>

		<div class="wrap">
			<?php
			// Just get out if nothing else matters...
			$api = mailchimp_sf_get_api();
			if ( ! $api ) {
				return;
			}
			?>
			<div class="mailchimp-sf-settings-page-wrapper">
				<div class="mailchimp-sf-settings-page">
					<hr class="wp-header-end" />
					<?php settings_errors(); ?>
					<div class="mailchimp-sf-settings-list-wrapper">
						<p class="mailchimp-sf-settings-list-note">
							<?php esc_html_e( 'Please select the Mailchimp list you\'d like to connect to your form.', 'mailchimp' ); ?>
						</p>
						<p class="mailchimp-sf-settings-list-description">
							<strong><?php esc_html_e( 'Note:', 'mailchimp' ); ?></strong> <?php esc_html_e( 'List settings are fetched from Mailchimp, fetching a new list will override your current settings.', 'mailchimp' ); ?>
						</p>
						<form method="post" action="<?php echo esc_url( add_query_arg( array( 'page' => 'mailchimp_sf_options' ), admin_url( 'admin.php' ) ) ); ?>">
							<?php
							// we *could* support paging, but few users have that many lists (and shouldn't)
							$lists = $api->get( 'lists', 100, array( 'fields' => 'lists.id,lists.name' ) );
							if ( is_wp_error( $lists ) ) {
								$msg = sprintf(
									/* translators: %s: error message */
									esc_html__( 'Uh-oh, we couldn\'t get your lists from Mailchimp! Error: %s', 'mailchimp' ),
									esc_html( $lists->get_error_message() )
								);
								admin_notice_error( $msg );
							} elseif ( isset( $lists['lists'] ) && count( $lists['lists'] ) === 0 ) {
								$msg = sprintf(
									/* translators: %s: link to Mailchimp */
									esc_html__( 'Uh-oh, you don\'t have any lists defined! Please visit %s, login, and setup a list before using this tool!', 'mailchimp' ),
									"<a href='http://www.mailchimp.com/'>Mailchimp</a>"
								);
								admin_notice_error( $msg );
							} else {
								$lists            = $lists['lists'];
								$option           = get_option( 'mc_list_id' );
								$list_ids         = array_map(
									function ( $ele ) {
										return $ele['id'];
									},
									$lists
								);
								$is_list_selected = in_array( $option, $list_ids, true );
								?>
								<div class="mailchimp-sf-settings-list-select-wrapper">
									<div class="mailchimp-sf-settings-list-select">
										<label class="screen-reader-text" for="mc_list_id"><?php esc_html_e( 'Select a list', 'mailchimp' ); ?></label>
										<select id="mc_list_id" name="mc_list_id">
											<option value=""> &mdash; <?php esc_html_e( 'Select A List', 'mailchimp' ); ?> &mdash; </option>
											<?php
											foreach ( $lists as $list ) {
												?>
												<option value="<?php echo esc_attr( $list['id'] ); ?>"<?php selected( $list['id'], $option ); ?>><?php echo esc_html( $list['name'] ); ?></option>
												<?php
											}
											?>
										</select>
									</div>
									<div class="mailchimp-sf-settings-list-select-button">
										<input type="hidden" name="mcsf_action" value="update_mc_list_id" />
										<?php wp_nonce_field( 'update_mc_list_id_action', 'update_mc_list_id_nonce' ); ?>
										<input type="submit" name="submit" value="<?php esc_attr_e( 'Fetch list settings', 'mailchimp' ); ?>" class="mailchimp-sf-button btn-secondary" />
									</div>
								</div>
								<?php
							} //end select list
							?>
						</form>
					</div>

					<?php
					// Just get out if nothing else matters...
					if ( ! $is_list_selected ) {
						?>
						</div></div></div></div>
						<?php
						return;
					}

					/**
					 * Render the user sync status, start cta etc...
					 */
					do_action( 'mailchimp_sf_user_sync_before_form' );

					// Load the form settings.
					include_once MCSF_DIR . 'includes/admin/templates/setup-page.php';

					// Load the user sync settings.
					include_once MCSF_DIR . 'includes/admin/templates/user-sync.php';
					?>
				</div>
			</div>
		</div>
		<?php
	}
	?>
</div>
