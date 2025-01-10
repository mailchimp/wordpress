/* eslint-disable no-undef */
describe('Validate unrequired fields', () => {
	let shortcodePostURL;
	let blockPostPostURL;

	before(() => {
		// TODO: Initialize tests from a blank state
		// TODO: Wipe WP data related to a users options
		// TODO: Delete all contacts in a users Mailchimp account
		// TODO: Ensure the default audience list is "10up"
		// TODO: Include all merge fields as "Visible" in the users Mailchimp account

		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then((urls) => {
			shortcodePostURL = urls.shortcodePostURL;
			blockPostPostURL = urls.blockPostPostURL;
		});

		cy.login(); // WP
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		// Set all merge fields to not required in the Mailchimp test user account
		cy.getListId('10up').then((listId) => {
			cy.updateMergeFieldsByList(listId, { required: false }).then(() => {
				cy.selectList('10up'); // Ensure list is selected, refreshes Mailchimp data with WP
			});
		});

		// Enable all merge fields
		cy.toggleMergeFields('check');
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	});

	after(() => {
		// I don't know why we need to login again, but we do
		cy.login(); // WordPress login

		// Cleanup
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.toggleMergeFields('uncheck'); // TODO: Do I need to uncheck all merge fields?

		// Re-enable JavaScript support
		cy.get('#mc_use_javascript').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	});

	function unrequiredFieldsSubmitWhileBlank() {
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_signup').should('exist');
			cy.get('#mc_mv_EMAIL').should('exist');
			cy.get('#mc_signup_submit').should('exist');

			// Optional merge fields
			cy.get('#mc_mv_FNAME').should('exist');
			cy.get('#mc_mv_LNAME').should('exist');
			cy.get('#mc_mv_ADDRESS-addr1').should('exist'); // Address line 1
			cy.get('#mc_mv_ADDRESS-addr2').should('exist'); // Address line 2
			cy.get('#mc_mv_ADDRESS-city').should('exist'); // City
			cy.get('#mc_mv_ADDRESS-state').should('exist'); // State
			cy.get('#mc_mv_ADDRESS-zip').should('exist'); // ZIP code
			cy.get('#mc_mv_ADDRESS-country').should('exist'); // Country
			cy.get('#mc_mv_BIRTHDAY').should('exist');
			cy.get('#mc_mv_COMPANY').should('exist');
			cy.get('#mc_mv_PHONE').should('exist');

			// Validation assertions

			// Email is required
			cy.get('#mc_mv_EMAIL').type('testemailuser1234@10up.com');

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

	context('JavaScript Disabled', () => {
		before(() => {
			cy.setJavaScriptOption(false);
		});

        it('Unrequired fields can be submitted while blank', unrequiredFieldsSubmitWhileBlank);
	});

	context.skip('JavaScript Enabled', () => {
		before(() => {
			cy.setJavaScriptOption(true);
		});

        it('Unrequired fields can be submitted while blank', unrequiredFieldsSubmitWhileBlank);
	});
});