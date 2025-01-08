<?php

use PHPUnit\Framework\TestCase;
use Mockery;
use Mailchimp\WordPress\Includes\Validation\Validate_Merge_Fields;
use Mailchimp\WordPress\Includes\Validation\Mailchimp_Validation;
use WP_Error;

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
		$validate_merge_fields = new Validate_Merge_Fields();
		$result = $validate_merge_fields->validate_phone($phoneNumArray, $formData);

		$expected = implode('-', $phoneNumArray); // Phone number connected with hyphen
		$this->assertEquals($expected, $result);
	}

	/**
	 * Test invalid phone numbers.
	 *
	 * @dataProvider invalidPhoneNumbersProvider
	 */
	public function testInvalidPhoneNumbers($phoneArr, $formData): void {
		// Step 1: Create a blank mocked WP_Error.
		$wp_error = Mockery::mock( 'WP_Error' );

		// Step 2: Mock the factory that creates WP_Error.
		$wp_error_factory = function ( $code, $message, $data = null ) use ( $wp_error ) {
			// Step 3: Make assertions against the properties we want the WP_Error object to contain
			// and dynamically fill them to return what our validate functions will pass in.
			$wp_error
				->shouldReceive( 'get_error_code' )
				->andReturn( $code );
			$wp_error
				->shouldReceive( 'get_error_message' )
				->with( $code )
				->andReturn( $message );
			return $wp_error;
		};

		// Step 4: Run the validation
		$validate_merge_fields = new Validate_Merge_Fields( $wp_error_factory );
		$result = $validate_merge_fields->validate_phone($phoneArr, $formData);

		// Step 5: Assert that what our validation logic does is what we expect it does

		// Is WP_Error
		$this->assertInstanceOf(WP_Error::class, $result);

		// Error code
		$this->assertEquals(Validate_Merge_Fields::PHONE_VALIDATION_ERROR_CODE, $result->get_error_code());

		// Error message
		$this->assertMatchesRegularExpression(
			'/must consist of only numbers/',
			$result->get_error_message(Validate_Merge_Fields::PHONE_VALIDATION_ERROR_CODE)
		);
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
