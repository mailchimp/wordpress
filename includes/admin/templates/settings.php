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

		?>
		<div class="wrap">
			<hr class="wp-header-end" />
			<?php settings_errors(); ?>
			<table class="mc-user" cellspacing="0">
				<tr>
					<td><h3><?php esc_html_e( 'Logged in as', 'mailchimp' ); ?>: <?php echo esc_html( $user['username'] ); ?></h3>
					</td>
					<td>
						<form method="post" action="" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure you want to log out?', 'mailchimp' ) ); ?>');">
							<input type="hidden" name="mcsf_action" value="logout"/>
							<input type="submit" name="Submit" value="<?php esc_attr_e( 'Logout', 'mailchimp' ); ?>" class="button button-secondary mailchimp-sf-button small" />
							<?php wp_nonce_field( 'mc_logout', '_mcsf_nonce_action' ); ?>
						</form>
					</td>
				</tr>
			</table>
			<?php
			// Just get out if nothing else matters...
			$api = mailchimp_sf_get_api();
			if ( ! $api ) {
				return;
			}
			?>

			<div class="mailchimp-sf-settings-page">
				<h3 class="mc-h2"><?php esc_html_e( 'Your Lists', 'mailchimp' ); ?></h3>
				<div>
					<p class="mc-p"><?php esc_html_e( 'Please select the Mailchimp list you\'d like to connect to your form.', 'mailchimp' ); ?></p>
					<p class="mc-list-note"><strong><?php esc_html_e( 'Note:', 'mailchimp' ); ?></strong> <?php esc_html_e( 'Updating your list will not remove list settings in this plugin, but changing lists will.', 'mailchimp' ); ?></p>

					<form method="post" action="<?php echo esc_url( add_query_arg( array( 'page' => 'mailchimp_sf_options' ), admin_url( 'admin.php' ) ) ); ?>">
						<?php
						// we *could* support paging, but few users have that many lists (and shouldn't)
						$lists = $api->get( 'lists', 100, array( 'fields' => 'lists.id,lists.name,lists.email_type_option' ) );
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
							<table class="mc-list-select" cellspacing="0">
								<tr class="mc-list-row">
									<td>
										<label class="screen-reader-text" for="mc_list_id"><?php esc_html_e( 'Select a list', 'mailchimp' ); ?></label>
										<select id="mc_list_id" name="mc_list_id" style="min-width:200px;">
											<option value=""> &mdash; <?php esc_html_e( 'Select A List', 'mailchimp' ); ?> &mdash; </option>
											<?php
											foreach ( $lists as $list ) {
												?>
												<option value="<?php echo esc_attr( $list['id'] ); ?>"<?php selected( $list['id'], $option ); ?>><?php echo esc_html( $list['name'] ); ?></option>
												<?php
											}
											?>
										</select>
									</td>
									<td>
										<input type="hidden" name="mcsf_action" value="update_mc_list_id" />
										<input type="submit" name="Submit" value="<?php esc_attr_e( 'Update List', 'mailchimp' ); ?>" class="button mailchimp-sf-button small" />
									</td>
								</tr>
							</table>
							<?php
						} //end select list
						?>
					</form>
				</div>

				<br/>

				<?php
				// Just get out if nothing else matters...
				if ( ! $is_list_selected ) {
					return;
				}

				$current_tab = empty( $_GET['tab'] ) ? 'settings' : sanitize_title( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$tabs        = array(
					'settings'  => __( 'Settings', 'mailchimp' ),
					'user_sync' => __( 'User Sync', 'mailchimp' ),
				)
				?>
				<nav class="mailchimp-sf-nav-tab-wrapper nav-tab-wrapper wp-clearfix">
					<?php
					foreach ( $tabs as $slug => $label ) {
						echo '<a href="' . esc_url( admin_url( 'admin.php?page=mailchimp_sf_options&tab=' . esc_attr( $slug ) ) ) . '" class="nav-tab ' . ( $current_tab === $slug ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
					}
					?>
				</nav>
				<?php
				// Show the selected tab.
				switch ( $current_tab ) {
					case 'settings':
						include_once MCSF_DIR . 'includes/admin/templates/setup-page.php';
						break;
					case 'user_sync':
						include_once MCSF_DIR . 'includes/admin/templates/user-sync.php';
						break;
					default:
						break;
				}
				?>
			</div>
		</div>
		<?php
	}
	?>
</div>
