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
		// // Repeated characters - allowing for vanity phone edge cases, seems like overkill to validate
		// [['111', '111', '1111'], ['name' => 'Phone']],

		// Special characters
		[['123', '!@#', '7890'], ['name' => 'Phone']],
		// Alphabets
		[['abc', '456', '7890'], ['name' => 'Phone']],
		// Mixed alphabets and numbers
		[['12a', '456', '7b90'], ['name' => 'Phone']],
		// Symbols like hyphens, slashes, or parentheses
		[['123-', '456', '7890'], ['name' => 'Phone']],
		[['123', '/456', '7890'], ['name' => 'Phone']],
		[['(12', '456', '7890'], ['name' => 'Phone']],
		// Decimal points
		[['1.4', '567', '8901'], ['name' => 'Phone']],
		// Emoji or Unicode characters
		[['123', '📞46', '7890'], ['name' => 'Phone']],
	];
}

	/**
	 * Data provider for too short phone numbers.
	 *
	 * @return array
	 */
	public static function tooShortPhoneNumbersProvider(): array {
		return [
			// [['', '', ''], ['name' => 'Phone']], // All empty is not an error, this is a black phone field

			[['12 ', '456', '789'], ['name' => 'Phone']], // 1st box whitespace
			[['123', '45 ', '7890'], ['name' => 'Phone']], // 2nd box whitespace
			[['123', '456', ' 890'], ['name' => 'Phone']], // 3rd box whitespace
			[['12', '456', '7890'], ['name' => 'Phone']], // 1st box short
			[['123', '56', '7890'], ['name' => 'Phone']], // 2nd box short
			[['123', '456', '890'], ['name' => 'Phone']], // 3rd box short
			[[null, '456', '7890'], ['name' => 'Phone']], // Null values
			[['123', "\r0", '7890'], ['name' => 'Phone']], // Control characters (tab)
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
		$wp_error = Mockery::mock('WP_Error');

		// Step 2: Mock the factory that creates WP_Error.
		$wp_error_factory = function ( $code, $message, $data = null ) use ( $wp_error ) {
			// Step 3: Make assertions against the properties we want the WP_Error object to contain
			// and dynamically fill them to return what our validate functions will pass in.
			$wp_error
				->shouldReceive('get_error_code')
				->andReturn($code);
			$wp_error
				->shouldReceive('get_error_message')
				->with($code)
				->andReturn($message);
			return $wp_error;
		};

		// Step 4: Run the validation
		$validate_merge_fields = new Validate_Merge_Fields( $wp_error_factory );
		$result = $validate_merge_fields->validate_phone($phoneArr, $formData);

		// Step 5: Assert that what our validation logic does is what we expect it does

		// Is WP_Error
		$this->assertInstanceOf(WP_Error::class, $result, "Result (Not WP_Error): {$result}");

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
	 * NOTE: No reason to test phone numbers that are too long
	 * because the input field limits the length a user can enter.
	 *
	 * @dataProvider tooShortPhoneNumbersProvider
	 */
	public function testTooShortPhoneNumbers($input, $data): void {
		$wp_error = Mockery::mock('WP_Error');
		$wp_error_factory = function ($code, $message, $data = null) use ($wp_error) {
			$wp_error
				->shouldReceive('get_error_code')
				->andReturn($code);
			$wp_error
				->shouldReceive('get_error_message')
				->with($code)
				->andReturn($message);
			return $wp_error;
		};

		$validate_merge_fields = new Validate_Merge_Fields($wp_error_factory);
		$result = $validate_merge_fields->validate_phone($input, $data);

		$this->assertInstanceOf(WP_Error::class, $result);
		$this->assertEquals(Validate_Merge_Fields::PHONE_VALIDATION_ERROR_CODE, $result->get_error_code());
		$this->assertMatchesRegularExpression(
			'/must contain the correct amount of digits/',
			$result->get_error_message(Validate_Merge_Fields::PHONE_VALIDATION_ERROR_CODE)
		);
	}
}
