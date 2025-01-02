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
		toggleMergeFields('uncheck');
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	});

	after(() => {
		// Cleanup
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Re-enable JavaScript support
        cy.setJavaScriptOption(true);
	});

	// Function to toggle merge fields
	function toggleMergeFields(action) {
		mergeFields.forEach((field) => {
			cy.get(field).should('exist')[action]();
		});
	}

	function invalidEmailAssertions() {
        [shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);

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
        });
	}

	context('JavaScript Disabled', () => {
		before(() => {
			cy.setJavaScriptOption(false);
		});

        it('Invalid email addresses fail validation', invalidEmailAssertions);
	});

	context('JavaScript Enabled', () => {
		before(() => {
			cy.login();
			cy.setJavaScriptOption(true);
		});

        it('Invalid email addresses fail validation', invalidEmailAssertions);
	});
});