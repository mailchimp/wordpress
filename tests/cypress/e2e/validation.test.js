/* eslint-disable no-undef */
describe('Form submission validation settings', () => {
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

		cy.login();

		// Log into Mailchimp account if we need to.
		cy.get('body').then(($body) => {
			const hasLogout = $body.find('input[value="Logout"]').length > 0;
			if (!hasLogout) {
				cy.mailchimpLogin();
			} else {
				cy.log('Already logged into Mailchimp account');
			}
		});
	});

	/**
	 * JS Support - No JS
	 * - Can submit the form and processes user input
	 * - Error handling mechanisms are in place to notify the user of submission issues
	 * - NOTE: Cypress doesn't have any built in ways to disable JS and the workarounds with
	 * cy.intercept didn't seem comprehensive
	 */
	it('Form submission and error handling works when JavaScript Support is disabled', () => {
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
	it('Form submission and error handling works when JavaScript Support is enabled', () => {
		// Is this already covered by the other tests?
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
	
			// TODO: Write more assertions for field validation
	
			// Test email error handling
			cy.get('#mc_signup_submit').click();
			cy.get('.mc_error_msg').should('exist');
			cy.get('.mc_error_msg').contains('Email Address: This value should not be blank.');
	
			// Test street address error handling
			// TODO: Test address line 2, city, state, zip/postal, country
	
			// Test birthday
	
			// Test company
	
			// Test first name
	
			// Test last name
	
			// Test phone number
		});
	}

});