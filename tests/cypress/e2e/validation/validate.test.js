/* eslint-disable no-undef */
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
			cy.updateMergeFields(listId, { required: false });
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