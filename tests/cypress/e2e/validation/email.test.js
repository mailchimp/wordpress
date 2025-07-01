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
	let blockPostPostURL;
	const invalidEmailErrorRegex = /please.*valid email/i; // please...valid email

	before(() => {
		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then((urls) => {
			({ blockPostPostURL } = urls);
		});

		cy.login(); // WP
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		// Set all merge fields to not required in the Mailchimp test user account
		cy.setMergeFieldsRequired(false);

		// Disable all merge fields
		cy.toggleMergeFields('uncheck');
		cy.get('#mc_mv_FNAME').trigger('change');
		cy.get('input[value="Save Changes"]:visible').first().click();
	});

	it('Invalid email addresses fail validation', () => {
		cy.visit(blockPostPostURL);

		// Ensure the form exists
		cy.get('#mc_signup').should('exist');
		cy.get('#mc_mv_EMAIL').should('exist');
		cy.get('#mc_signup_submit').should('exist');

		// Email assertions
		cy.get('#mc_mv_EMAIL').clear(); // No email
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains('Email Address: This value should not be blank.');

		cy.get('#mc_mv_EMAIL').clear().type('user@'); // Missing domain
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains(invalidEmailErrorRegex);

		cy.get('#mc_mv_EMAIL').clear().type('@example.com'); // Missing username
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains(invalidEmailErrorRegex);

		cy.get('#mc_mv_EMAIL').clear().type('userexample.com'); // Missing '@' symbol
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains(invalidEmailErrorRegex);

		cy.get('#mc_mv_EMAIL').clear().type('user..name@example.com'); // Consecutive dots
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains(invalidEmailErrorRegex);

		cy.get('#mc_mv_EMAIL').clear().type('user!#%&*{}@example.com'); // Invalid characters
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains(invalidEmailErrorRegex);

		cy.get('#mc_mv_EMAIL').clear().type('user@example'); // Missing top-level domain
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains(invalidEmailErrorRegex);

		cy.get('#mc_mv_EMAIL').clear().type('user@-example.com'); // Domain starting with dash
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains(invalidEmailErrorRegex);

		// TODO: Mailchimp accepts this. Is this a bug?
		// cy.get('#mc_mv_EMAIL').clear().type('user@example-.com'); // Domain ending with dash
		// cy.submitFormAndVerifyError();
		// cy.get('.mc_error_msg').contains(invalidEmailErrorRegex);

		cy.get('#mc_mv_EMAIL').clear().type('"user@example.com'); // Unclosed quoted string
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains(invalidEmailErrorRegex);

		// Test exceeding maximum email length
		let longEmail = 'a'.repeat(245) + '@example.com';
		cy.get('#mc_mv_EMAIL').clear().type(longEmail);
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains(invalidEmailErrorRegex);
	});
});
