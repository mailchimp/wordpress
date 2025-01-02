/* eslint-disable no-undef */

/**
 * NOTE: We aren't verifying successful submission in the Mailchimp account for this test suite.
 * Those assertions are covered in the submission tests.
 *
 * Hypothetically, it's possible that validation passes WP, but fails in Mailchimp.
 *
 * However, the response the API receives comes directly from Mailchimp and is displayed
 * on the FE. So, if the form is submitted successfully, the submission should be in Mailchimp.
 */
describe('Validate merge field conditions and error handling', () => {
	let shortcodePostURL;
	let blockPostPostURL;

	// Merge fields array for reuse
	const mergeFields = [
		'#mc_mv_FNAME',
		'#mc_mv_LNAME',
		'#mc_mv_ADDRESS',
		'#mc_mv_BIRTHDAY',
		'#mc_mv_COMPANY',
		'#mc_mv_PHONE'
	];

	before(() => {
		// TODO: Initialize tests from a blank state
		// TODO: Wipe WP data related to a users options
		// TODO: Delete all contacts in a users Mailchimp account
		// TODO: Ensure the default audience list is "10up"
		// TODO: Include all merge fields as "Visible" in the users Mailchimp account

		// Load the post URLs from the JSON file
		cy.fixture('postUrls.json').then((urls) => {
			shortcodePostURL = urls.shortcodePostURL;
			blockPostPostURL = urls.blockPostPostURL;
		});

		cy.login(); // WP
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		// Set all merge fields to not required in the Mailchimp test user account
		cy.getListId('10up').then((listId) => {
			cy.updateMergeFieldsByList(listId, { required: false });
		});

		cy.selectList('10up'); // Ensure list is selected, refreshes Mailchimp data with WP

		// Enable all merge fields
		toggleMergeFields('check');
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	});

	after(() => {
		// Cleanup
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		toggleMergeFields('uncheck'); // TODO: Do I need to uncheck all merge fields?

		// Re-enable JavaScript support
		cy.get('#mc_use_javascript').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	});

	function randomXDigiNumber(x) {
		return Number(Array.from({ length: x }, () => Math.floor(Math.random() * 10)).join(''));
	}

	// Function to toggle merge fields
	function toggleMergeFields(action) {
		mergeFields.forEach((field) => {
			cy.get(field).should('exist')[action]();
		});
	}

	function formValidationAssertions() {
		// Verify No JS form submission
		// TODO: Modularize form validation assertions?
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_signup').should('exist');
			cy.get('#mc_mv_EMAIL').should('exist');
			cy.get('#mc_signup_submit').should('exist');
	
			// Optional merge fields
			cy.get('#mc_mv_FNAME').should('exist');
			cy.get('#mc_mv_LNAME').should('exist');
			cy.get('#mc_mv_ADDRESS-addr1').should('exist'); // The address field has several inputs
			cy.get('#mc_mv_BIRTHDAY').should('exist');
			cy.get('#mc_mv_COMPANY').should('exist');
			cy.get('#mc_mv_PHONE').should('exist');

			// Validation assertions
	
			// Test email error handling
			cy.submitFormAndVerifyError();
			cy.get('.mc_error_msg').contains('Email Address: This value should not be blank.');

			/**
			 * Phone Number
			 */

			// Setup
			// Set the US phone format and required
			cy.getListId('10up').then((listId) => {
				cy.updateMergeFieldByTag(listId, 'PHONE', { required: true, options: { phone_format: 'US' } });
			});
			cy.selectList('10up'); // Ensure list is selected, refreshes Mailchimp data with WP

			// - If US phone format, phone number should be at least 12 chars (10 digits and two hyphens)
			cy.get('#mc_mv_EMAIL').type(`testingemail${randomXDigiNumber(10)}@gmail.com`); // TODO: This is sloppy, but it's a quick fix for now
			cy.get('#mc_mv_PHONE').type('123456789'); // one digit short
			cy.submitFormAndVerifyError();
			cy.get('.mc_error_msg').contains('must consist of only numbers');

			/**
			 * Assertions for phone number length validation
			 */
			const tooShortPhones = ['123456789', '123-456-78', '12345-678']; // Less than 10 digits
			const tooLongPhones = ['12345678901', '123-4567-8901', '1234-567-8901']; // More than 10 digits
			const validLengthPhones = ['123-456-7890', '1234567890']; // Exactly 10 digits

			validLengthPhones.forEach((phone) => {
				it(`should accept phone number with valid length: ${phone}`, () => {
					cy.get('#mc_mv_EMAIL').type(`validemail${randomXDigiNumber(10)}@gmail.com`);
					cy.get('#mc_mv_PHONE').clear().type(phone);
					cy.submitFormAndVerifyWPSuccess();
				});
			});

			tooShortPhones.forEach((phone) => {
				it(`should reject phone number that is too short: ${phone}`, () => {
					cy.get('#mc_mv_EMAIL').type(`shortemail${randomXDigiNumber(10)}@gmail.com`);
					cy.get('#mc_mv_PHONE').clear().type(phone);
					cy.submitFormAndVerifyError();
					cy.get('.mc_error_msg').contains('Phone number is too short');
				});
			});

			tooLongPhones.forEach((phone) => {
				it(`should reject phone number that is too long: ${phone}`, () => {
					cy.get('#mc_mv_EMAIL').type(`longemail${randomXDigiNumber(10)}@gmail.com`);
					cy.get('#mc_mv_PHONE').clear().type(phone);
					cy.submitFormAndVerifyError();
					cy.get('.mc_error_msg').contains('Phone number is too long');
				});
			});

			// - If US phone format, US phone pattern must be (/[0-9]{0,3}-[0-9]{0,3}-[0-9]{0,4}/A)
			const validPhones = ['123-456-7890'];
			const invalidPhones = ['123-456-789', '12-345-67890', '123-45-67890', '1234-56-7890', '123-4567-890', '123-456-789a', '123-456-78@0', '1234567890'];

			invalidPhones.forEach((phone) => {
				cy.get('#mc_mv_EMAIL').type(`testingemail${randomXDigiNumber(10)}@gmail.com`); // TODO: This is sloppy, but it's a quick fix for now
				cy.get('#mc_mv_PHONE').clear().type(phone);
				cy.submitFormAndVerifyError();
				cy.get('.mc_error_msg').contains('must consist of only numbers');
			});

			validPhones.forEach((phone) => {
				cy.get('#mc_mv_EMAIL').type(`testingemail${randomXDigiNumber(10)}@gmail.com`); // TODO: This is sloppy, but it's a quick fix for now
				cy.get('#mc_mv_PHONE').clear().type(phone);
				cy.submitFormAndVerifyWPSuccess();
			});

			// Cleanup
			cy.getListId('10up').then((listId) => {
				cy.updateMergeFieldByTag(listId, 'PHONE', { required: false, options: { phone_format: 'international' } });
			});
			cy.selectList('10up'); // Ensure list is selected, refreshes Mailchimp data with WP
	
			/**
			 * Address - Handled in /validation/address.test.js
			 */

			/**
			 * Remaining merge fields
			 */
			// Test birthday - no validation
			// Test company - no validation
			// Test first name - no validation
			// Test last name - no validation
		});
	}

	it('JavaScript disabled', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Disable JavaScript support
		cy.get('#mc_use_javascript').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		formValidationAssertions();
	});

	it('JavaScript enabled', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Enable JavaScript support
		cy.get('#mc_use_javascript').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		formValidationAssertions();
	});
});