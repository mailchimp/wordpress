<?php
/**
 * Test Address validation
 *
 * @package mailchimp
 */

use \WP_Mock\Tools\TestCase;

/**
 * AddressValidationTest class tests the address validation.
 */
class AddressValidationTest extends TestCase {
	/**
	 * instance of mailchimp class.
	 *
	 * @var object
	 */
	private $instance;

	/**
	 * Set up our mocked WP functions. Rather than setting up a database we can mock the returns of core WordPress functions.
	 *
	 * @return void
	 */
	public function setUp(): void {
		\WP_Mock::setUp();
		// $this->instance = new SafeSvg\safe_svg(); // TODO: Delete this, I don't think we need to instantiate any classes for our test
	}

	/**
	 * Tear down WP Mock.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * Test allow_svg function.
	 *
	 * @return void
	 */
	public function test_tests_are_running() {
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