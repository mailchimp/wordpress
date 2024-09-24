<?php
/**
 * Main settings page template
 *
 * @package Mailchimp
 */

$user         = get_option( 'mc_user' );
$is_logged_in = ! ( ! $user || ( ! get_option( 'mc_api_key' ) && ! mailchimp_sf_get_access_token() ) );
?>
<div id="mailchimp-sf-settings-page">
	<?php
	// Header.
	include_once MCSF_DIR . 'includes/admin/templates/header.php'; // phpcs:ignore PEAR.Files.IncludingFile.UseRequireOnce

	// If user is not logged in, show login form.
	if ( ! $is_logged_in ) {
		include_once MCSF_DIR . 'includes/admin/templates/login.php';
	} else {
		include_once MCSF_DIR . 'views/setup_page.php';
	}
	?>
</div>
