/* eslint-disable no-undef */
describe('Form submission, validation, and error handling', () => {
	let shortcodePostURL;
	let blockPostPostURL;

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
	});

	/**
	 * JS Support - No JS
	 * - Can submit the form and processes user input
	 * - Error handling mechanisms are in place to notify the user of submission issues
	 * - NOTE: Cypress doesn't have any built in ways to disable JS and the workarounds with
	 * cy.intercept didn't seem comprehensive
	 */
	it('JavaScript Support is disabled', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Ensure that JavaScript support is disabled
		cy.get('#mc_use_javascript').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
		
		// Enable all merge fields to test validation later
		cy.get('#mc_mv_FNAME').check();
		cy.get('#mc_mv_LNAME').check();
		cy.get('#mc_mv_ADDRESS').check();
		cy.get('#mc_mv_BIRTHDAY').check();
		cy.get('#mc_mv_COMPANY').check();
		cy.get('#mc_mv_PHONE').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		cy.get('input[value="Update List"]').click();

		formValidationAssertions();

		// Cleanup
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Uncheck all optional merge fields
		cy.get('#mc_mv_FNAME').uncheck();
		cy.get('#mc_mv_LNAME').uncheck();
		cy.get('#mc_mv_ADDRESS').uncheck();
		cy.get('#mc_mv_BIRTHDAY').uncheck();
		cy.get('#mc_mv_COMPANY').uncheck();
		cy.get('#mc_mv_PHONE').uncheck();
		
		cy.get('#mc_use_javascript').check(); // Re-enable JS support
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	});

	// JS Support - Yes JS
	// Can submit the form and processses user input
	// Error handling mechanisms are in place to notify user of submission issues
	it('JavaScript Support is enabled', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Ensure that JavaScript support is disabled
		cy.get('#mc_use_javascript').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
		
		// Enable all merge fields to test validation later
		cy.get('#mc_mv_FNAME').check();
		cy.get('#mc_mv_LNAME').check();
		cy.get('#mc_mv_ADDRESS').check();
		cy.get('#mc_mv_BIRTHDAY').check();
		cy.get('#mc_mv_COMPANY').check();
		cy.get('#mc_mv_PHONE').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		cy.get('input[value="Update List"]').click();

		formValidationAssertions();

		// Cleanup
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Uncheck all optional merge fields
		cy.get('#mc_mv_FNAME').uncheck();
		cy.get('#mc_mv_LNAME').uncheck();
		cy.get('#mc_mv_ADDRESS').uncheck();
		cy.get('#mc_mv_BIRTHDAY').uncheck();
		cy.get('#mc_mv_COMPANY').uncheck();
		cy.get('#mc_mv_PHONE').uncheck();
		
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	});

	// Form validation - make modular and can run in both JS and non JS setups

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
			cy.get('#mc_signup_submit').click();
			cy.get('.mc_error_msg').should('exist');
			cy.get('.mc_error_msg').contains('Email Address: This value should not be blank.');

			// TODO: BLOCKED - Test phone number
			// Blocked until we standardize testing data. We must be able to set the phone format to US.
			// Default is international. 

			// - If US phone format, phone number should be at least 12 chars (10 digits and two hyphens)
			// cy.get('#mc_mv_PHONE').type('123456789'); // one digit short
			// cy.get('#mc_signup_submit').click();
			// cy.get('.mc_error_msg').should('exist');
			// cy.get('.mc_error_msg').contains('must consist of only numbers');

			// - If US phone format, US phone pattern must be (/[0-9]{0,3}-[0-9]{0,3}-[0-9]{0,4}/A)
	
			// Test street address error handling
			// TODO: BLOCKED - Test address line 2, city, state, zip/postal, country
			// Blocked until we standardize testing data. The address must be required inside
			// the Mailchimp account
			// - If required, Addr 1 and city must not be empty

			// Test birthday - no validation
	
			// Test company - no validation
	
			// Test first name - no validation
	
			// Test last name - no validation

			// TODO: BLOCKED - Successful submission assertion here, but blocked until we standardize testing
			// data to clear after every test run
		});
	}

});