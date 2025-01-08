<?php

use PHPUnit\Framework\TestCase;
use Mockery;
use function Mailchimp\WordPress\Includes\Validation\merge_validate_phone;
use Mailchimp\WordPress\Includes\Validation\Mailchimp_Validation;

// Require files manually
// TODO: Remove this once we are using composer autoload
require_once TEST_PLUGIN_DIR . '/includes/validation/class-mailchimp-validation.php';
$validation = new Mailchimp_Validation();
$validation->init();

/**
 * USPhoneNumberValidationTest class tests phone number validation.
 */
class USPhoneNumberValidationTest extends TestCase {

	public function setUp(): void {
		parent::setUp();
		WP_Mock::setUp();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
		Mockery::close();
		parent::tearDown();
	}

	/**
	 * Data provider for valid phone numbers.
	 *
	 * @return array
	 */
	public static function validPhoneNumbersProvider(): array {
		return [
			[['123', '456', '7890'], ['name' => 'Phone']],
			[['987', '654', '3210'], ['name' => 'Phone']],
		];
	}

	/**
	 * Data provider for invalid phone numbers.
	 *
	 * @return array
	 */
	public static function invalidPhoneNumbersProvider(): array {
		return [
			[['123', '45!', '7890'], ['name' => 'Phone']],
			[['123', '456', '78a0'], ['name' => 'Phone']],
		];
	}

	/**
	 * Data provider for too short phone numbers.
	 *
	 * @return array
	 */
	public static function tooShortPhoneNumbersProvider(): array {
		return [
			[['12', '456', '789'], ['name' => 'Phone']],
			[['', '45', '7890'], ['name' => 'Phone']],
		];
	}

	/**
	 * Data provider for too long phone numbers.
	 *
	 * @return array
	 */
	public static function tooLongPhoneNumbersProvider(): array {
		return [
			[['1234', '567', '890'], ['name' => 'Phone']],
			[['123', '4567', '8901'], ['name' => 'Phone']],
		];
	}

	/**
	 * Test valid phone numbers.
	 *
	 * @dataProvider validPhoneNumbersProvider
	 */
	public function testValidPhoneNumbers($phoneNumArray, $formData): void {
		$result = merge_validate_phone($phoneNumArray, $formData);

		$expected = implode('-', $phoneNumArray); // Phone number connected with hyphen
		$this->assertEquals($expected, $result);
	}

	/**
	 * Test invalid phone numbers.
	 *
	 * @dataProvider invalidPhoneNumbersProvider
	 */
	public function testInvalidPhoneNumbers($phoneArr, $formData): void {
		// Use Mockery to mock the WP_Error class
		$mock = Mockery::mock('WP_Error');

		// Mock the `create_wp_error` function
		WP_Mock::userFunction('create_wp_error', [
			'times' => 1,
			'args' => ['mc_phone_validation', WP_Mock\Functions::type('string')],
			'return' => new WP_Error('mc_phone_validation', 'Mocked error message'),
		]);

		// $mock->shouldReceive('get_error_code')->andReturn('mc_phone_validation');

		// // Assert that WP_Error was constructed correctly
		// $mock->shouldReceive('__construct')
		// 	->with('mc_phone_validation', Mockery::pattern('/must consist of only numbers/'), null)
		// 	->once();

		// Function under test
		$result = merge_validate_phone($phoneArr, $formData);

		// echo var_dump($result->get_error_code());

		// Assert the function returned a WP_Error object
		// $this->assertInstanceOf(WP_Error::class, $result);
		// Assert the result is a WP_Error object
		$this->assertInstanceOf(WP_Error::class, $result);
		// $this->assertEquals('mc_phone_validation', $result->get_error_code());
	}

	/**
	 * Test too short phone numbers.
	 *
	 * @dataProvider tooShortPhoneNumbersProvider
	 */
	public function testTooShortPhoneNumbers($input, $data): void {
		$result = merge_validate_phone($input, $data);
		$this->assertInstanceOf(WP_Error::class, $result);
		$this->assertEquals('mc_phone_validation', $result->get_error_code());
		$this->assertStringContainsString('must contain the correct amount of digits', $result->get_error_message());
	}

	/**
	 * Test too long phone numbers.
	 *
	 * @dataProvider tooLongPhoneNumbersProvider
	 */
	public function testTooLongPhoneNumbers($input, $data): void {
		$result = merge_validate_phone($input, $data);
		$this->assertInstanceOf(WP_Error::class, $result);
		$this->assertEquals('mc_phone_validation', $result->get_error_code());
		$this->assertStringContainsString('must contain the correct amount of digits', $result->get_error_message());
	}
}
