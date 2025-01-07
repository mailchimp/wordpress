<?php
/**
 * Test Address validation
 *
 * @package mailchimp
 */

/**
 * Using PHP Unit TestCase due to fatal error when using WP_Mock test case
 * 
 * PHP Fatal error:  Cannot override final method PHPUnit\Framework\TestCase::run()
 * in /vendor/10up/wp_mock/php/WP_Mock/Tools/TestCase.php on line 299
 */
use PHPUnit\Framework\TestCase;
// use WP_Mock\Tools\TestCase;

/**
 * AddressValidationTest class tests the address validation.
 */
class USPhoneNumberValidationTest extends TestCase {

	/**
	 * Set up our mocked WP functions. Rather than setting up a database we can mock the returns of core WordPress functions.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	/**
	 * Tear down WP Mock.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test PHPUnit is working.
	 *
	 * @return void
	 */
	public function test_phpunit_working() {
        $this->assertTrue(true, 'This is a placeholder test to make sure the tests are running');

		// \WP_Mock::userFunction(
		// 	'get_option',
		// 	array(
		// 		'args'   => [ 'safe_svg_upload_roles', [] ],
		// 		'return' => [ 'editor' ],
		// 	)
		// );

		// \WP_Mock::userFunction(
		// 	'current_user_can',
		// 	array(
		// 		'args'   => 'safe_svg_upload_svg',
		// 		'return' => true,
		// 	)
		// );

		// $allowed_svg = $this->instance->allow_svg( array() );
		// $this->assertNotEmpty( $allowed_svg );
		// $this->assertContains( 'image/svg+xml', $allowed_svg );
	}
}