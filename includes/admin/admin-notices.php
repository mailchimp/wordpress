<?php
/**
 * Admin notices.
 *
 * @package Mailchimp
 */

namespace Mailchimp\WordPress\Includes\Admin;

/**
 * Display success admin notice.
 * This function is now a wrapper around the Mailchimp_Admin_Notices class, will be deprecated in future versions. Use that class instead.
 *
 * @since 1.7.0
 * @since 2.1.0 - Moved notice rendering to class-mailchimp-admin-notices.php
 *
 * @param string $msg The message to display.
 * @return void
 */
function admin_notice_success( string $msg ) {
	\Mailchimp_Admin_Notices::instance()->add( $msg, 'success' );
}

/**
 * Display error admin notice.
 * This function is now a wrapper around the Mailchimp_Admin_Notices class, will be deprecated in future versions. Use that class instead.
 *
 * @since 1.7.0
 * @since 2.1.0 - Moved notice rendering to class-mailchimp-admin-notices.php
 *
 * @param string $msg The message to display.
 * @return void
 */
function admin_notice_error( string $msg ) {
	\Mailchimp_Admin_Notices::instance()->add( $msg, 'error' );
}
