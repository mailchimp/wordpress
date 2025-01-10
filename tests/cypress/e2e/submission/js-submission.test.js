/* eslint-disable no-undef */
describe('JavaScript submission', () => {
	let blockPostPostURL;
	let mergeFields;

	before(() => {
		// TODO: Initialize tests from a blank state
		// TODO: Wipe WP data related to a users options
		// TODO: Delete all contacts in a users Mailchimp account
		// TODO: Ensure the default audience list is "10up"
		// TODO: Include all merge fields as "Visible" in the users Mailchimp account

		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then((urls) => {
			blockPostPostURL = urls.blockPostPostURL;
		});

		// Load the post URLs from the JSON file
		cy.fixture('mergeFields').then((fields) => {
			mergeFields = Object.values(fields); // Extract field selectors as an array
		});

		cy.login(); // WP
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		cy.selectList('10up'); // Ensure list is selected, refreshes Mailchimp data with WP

		cy.toggleMergeFields('uncheck');
	});

	beforeEach(() => {
		setUpForm();
	});

	function setUpForm() {
		cy.visit(blockPostPostURL);
	
		// Step 1: Assert form contains setup elements
		// Email
		cy.get('#mc_signup').should('exist');
		cy.get('#mc_mv_EMAIL').should('exist');
		cy.get('#mc_signup_submit').should('exist');
	
		// // Other merge fields (loaded from fixture)
		// mergeFields.forEach((field) => {
		// 	cy.get(field.selector).should('exist');
		// 	cy.get(field.selector).type(field.value);
		// });
	}

	it('Disables the submit button before attempting submission', () => {
		// Step 1: Visit the form page
		cy.visit(blockPostPostURL);

		// Step 2: Assert the submit button exists and is enabled initially
		cy.get('#mc_signup_submit').should('exist').and('be.enabled');

		// Step 3: Submit the form
		cy.get('#mc_signup_submit').click();

		// Step 4: Assert that the submit button is disabled after submitting the form
		cy.get('#mc_signup_submit').should('be.disabled');

		// Step 5: Verify that the form submission failed
		cy.get('.mc_error_msg').should('exist');
	});

	it.skip('Perform post submit actions after successful submission', () => {
		// Re-enable submit button
		// Display success message
		// Clear form data
		// Scroll to the top
	});

	it.skip('Persist form data on Mailchimp API validation failure', () => {

		// Confirm that we received an error
		cy.get('#mc_signup_submit').click();
		cy.get('.mc_error_msg').should('exist');
		cy.get('.mc_error_msg').contains('Email Address:');
	});

	it.skip('Success submission with JS support adds email to Mailchimp account as contact', () => {
		// // TODO: This is failing because we need to confirm the test email address subscription
		// // TODO: We will also have to delete the contact before each form submission via the Mailchimp API
		// Step 6: Verify that the form was submitted successfully
		// cy.submitFormAndVerifyWPSuccess();
	
		// // Step 7: Verify that the contact was added to the Mailchimp account via the Mailchimp API
		// cy.verifyContactAddedToMailchimp(email, '10up');
	})
});