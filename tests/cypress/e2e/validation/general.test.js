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
describe('General merge field validation', () => {
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

	// Function to toggle merge fields
	function toggleMergeFields(action) {
		mergeFields.forEach((field) => {
			cy.get(field).should('exist')[action]();
		});
	}

	function invalidEmailAssertions() {
		cy.get('#mc_mv_EMAIL').clear(); // No email
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains('Email Address: This value should not be blank.');

		cy.get('#mc_mv_EMAIL').clear().type('user@'); // Missing domain
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains('Email Address: Please enter a valid email.');

		cy.get('#mc_mv_EMAIL').clear().type('@example.com'); // Missing username
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains('Email Address: Please enter a valid email.');

		cy.get('#mc_mv_EMAIL').clear().type('userexample.com'); // Missing '@' symbol
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains('Email Address: Please enter a valid email.');

		cy.get('#mc_mv_EMAIL').clear().type('user..name@example.com'); // Consecutive dots
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains('Email Address: Please enter a valid email.');

		cy.get('#mc_mv_EMAIL').clear().type('user!#%&*{}@example.com'); // Invalid characters
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains('Email Address: Please enter a valid email.');

		cy.get('#mc_mv_EMAIL').clear().type('user@example'); // Missing top-level domain
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains('Email Address: Please enter a valid email.');

		cy.get('#mc_mv_EMAIL').clear().type('user@-example.com'); // Domain starting with dash
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains('Email Address: Please enter a valid email.');

		cy.get('#mc_mv_EMAIL').clear().type('user@example-.com'); // Domain ending with dash
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains('Email Address: Please enter a valid email.');

		cy.get('#mc_mv_EMAIL').clear().type('"user@example.com'); // Unclosed quoted string
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains('Email Address: Please enter a valid email.');

		// Test exceeding maximum email length
		let longEmail = 'a'.repeat(245) + '@example.com';
		cy.get('#mc_mv_EMAIL').clear().type(longEmail);
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains('Email Address: Please enter a valid email.');
	}

	function formValidationAssertions() {
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
			cy.get('#mc_mv_EMAIL').clear();
			cy.submitFormAndVerifyError();

			cy.get('.mc_error_msg').contains('Email Address: This value should not be blank.');
			invalidEmailAssertions();

			// TODO: BLOCKED - After a user fills out a form successfully once none of the verification checks work (is this a bug?)
			// TODO: We will have to delete the contact before each form submission via the Mailchimp API

			// // TODO: This is failing because we need to confirm the test email address subscription
			// // TODO: We will also have to delete the contact before each form submission via the Mailchimp API
			// Step 6: Verify that the form was submitted successfully
			// cy.submitFormAndVerifyWPSuccess();

			// // Step 7: Verify that the contact was added to the Mailchimp account via the Mailchimp API
			// cy.verifyContactAddedToMailchimp(email, '10up');

			/**
			 * Phone Number - Handled in /validation/us-phone.test.js
			 */

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