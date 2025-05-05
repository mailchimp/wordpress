/* eslint-disable no-undef */
import { generateRandomEmail } from '../../support/functions/utility';

describe('Validate unrequired fields', () => {
	let shortcodePostURL;
	let blockPostPostURL;

	before(() => {
		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then((urls) => {
			({ shortcodePostURL, blockPostPostURL } = urls);
		});

		cy.login(); // WP
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		// Set all merge fields to not required in the Mailchimp test user account
		cy.setMergeFieldsRequired(false);

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
	});

	it('Unrequired fields can be submitted while blank', () => {
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

			// Validation assertions

			// Email is required
			const email = generateRandomEmail('unrequired-validation-test');
			cy.get('#mc_mv_EMAIL').type(email);

			// Step 6: Verify that the form was submitted successfully
			cy.submitFormAndVerifyWPSuccess();

			// Step 7: Verify that the contact was added to the Mailchimp account via the Mailchimp API
			cy.verifyContactInMailchimp(email);

			// Step 8: Cleanup and delete contact
			cy.deleteContactFromList(email);

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
	});
});
