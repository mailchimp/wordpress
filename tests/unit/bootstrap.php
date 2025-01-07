<?php
/**
 * The bootstrap file for PHPUnit tests for the Mailchimp plugin.
 * Starts up WP_Mock and requires the files needed for testing.
 *
 * @package mailchimp
 */

define( 'TEST_PLUGIN_DIR', dirname( dirname( __DIR__ ) ) . '/' );

// First we need to load the composer autoloader so we can use WP Mock.
require_once TEST_PLUGIN_DIR . '/vendor/autoload.php';

// Now call the bootstrap method of WP Mock.
WP_Mock::bootstrap();

/**
 * Mock functions preventing plugin from loading
 */
// \WP_Mock::userFunction( 'plugin_dir_url' );
// \WP_Mock::userFunction( 'plugin_dir_path' );
// \WP_Mock::userFunction( 'plugins_url' );
// \WP_Mock::userFunction( 'trailingslashit' );
// \WP_Mock::userFunction( 'remove_filter' );
// (There are more)

/**
 * Strategy:
 * - Unit testing only (no integration or end-to-end).
 * - Mock WP core functions with WP_Mock (WP core is not loaded).
 * - Promote long-term maintainability through modularization.
 * 
 * Reasoning:
 * This plugin is not modular and initializing the plugin
 * will require mocking all of the WP core functions
 * used in order to avoid fatal errors on loading.
 * 
 * Instead, we'll require functionality as it's needed
 * by first:
 * 
 * 1) Modularizing the functionality into a namespaced file and then
 * 2) Importing it directly into the test.
 * 
 * End to end tests are handled in Cypress. Integration
 * and E2E tests should be handled in Cypress.
 */
// require TEST_PLUGIN_DIR . '/mailchimp.php';