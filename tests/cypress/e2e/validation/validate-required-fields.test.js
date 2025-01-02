/* eslint-disable no-undef */
describe('Validate required fields', () => {
	let shortcodePostURL;
	let blockPostPostURL;
	const email = 'testemail-neversubmitted5925082@10up.com';

	// (almost) the same in the WP admin as on the FE
	const requiredFields = [
		{ selector: '#mc_mv_FNAME', errorMessage: 'First Name:', input: 'Test' },
		{ selector: '#mc_mv_LNAME', errorMessage: 'Last Name:', input: 'User' },
		{ selector: '#mc_mv_ADDRESS-addr1', errorMessage: 'Address:', input: '123 Fake St.' }, // Address has sub fields on the FE form
		{ selector: '#mc_mv_ADDRESS-city', errorMessage: 'Address:', input: 'Nashville' }, // Address has sub fields on the FE form
		{ selector: '#mc_mv_ADDRESS-state', errorMessage: 'Address:', input: 'TN' }, // Address has sub fields on the FE form
		{ selector: '#mc_mv_ADDRESS-zip', errorMessage: 'Address:', input: '12345' }, // Address has sub fields on the FE form

		// Country is selected by default so no need to test this validation
		// { selector: '#mc_mv_ADDRESS-country', errorMessage: 'Address:', input: 'USA' }, // Address has sub fields on the FE form
		{ selector: '#mc_mv_BIRTHDAY', errorMessage: 'Birthday:', input: '01/10' },
		{ selector: '#mc_mv_COMPANY', errorMessage: 'Company:', input: '10up' },
		{ selector: '#mc_mv_PHONE', errorMessage: 'Phone Number:', input: '555-555-5555' },
	];

	before(() => {
		// Load the post URLs from the JSON file
		cy.fixture('postUrls.json').then((urls) => {
			shortcodePostURL = urls.shortcodePostURL;
			blockPostPostURL = urls.blockPostPostURL;
		});

		cy.login(); // WordPress login
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		// Set all merge fields to required in the Mailchimp test user account
		cy.getListId('10up').then((listId) => {
			cy.updateMergeFieldsByList(listId, { required: true });
		});

		cy.selectList('10up'); // Ensure list is selected, refreshes Mailchimp data with WP
	});

	after(() => {
		// Cleanup: Set all merge fields to not in the Mailchimp test user account
		cy.getListId('10up').then((listId) => {
			cy.updateMergeFieldsByList(listId, { required: false });
		});

		// TODO: Resync Mailchimp to WP data
		cy.selectList('10up'); // Ensure list is selected

		// Cleanup: Uncheck all optional merge fields
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_use_javascript').check(); // Cleanup: Check the JavaScript support box
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	});

	function validateRequiredFields(url) {
		cy.visit(url);

		// Ensure the form exists
		cy.get('#mc_signup').should('exist');

		// Test validation for each required field
		requiredFields.forEach((field) => {
			// Fill out entire form everytime so we can narrow tests to one input at a time
			fillOutAllFields();

			// Submit the form without input to trigger validation
			cy.get(field.selector).clear(); // Ensure field is empty
			cy.get('#mc_signup_submit').click();

			// Assert the error message is displayed
			cy.get('.mc_error_msg').should('exist');
			cy.get('.mc_error_msg').should('include.text', field.errorMessage);

			// Fill in the field
			cy.get(field.selector).type(field.input);
		});

		// TODO: BLOCKED - After a user fills out a form successfully once none of the verification checks work
		// TODO: We will have to delete the contact before each form submission via the Mailchimp API

		// // TODO: This is failing because we need to confirm the test email address subscription
		// // TODO: We will also have to delete the contact before each form submission via the Mailchimp API
		// Step 6: Verify that the form was submitted successfully
		// cy.submitFormAndVerifyWPSuccess();

		// // Step 7: Verify that the contact was added to the Mailchimp account via the Mailchimp API
		// cy.verifyContactAddedToMailchimp(email, '10up');
	}

	// TODO: Validation errors clear the entire form. We should fix this.
	// We could also significantly reduce the time this test takes by fixing this bug.
	function fillOutAllFields() {
		cy.get('#mc_mv_EMAIL').clear().type(email); // Email is always required
		requiredFields.forEach((field) => {
			cy.get(field.selector).clear().type(field.input);
			cy.get('body').click(0, 0); // Click outside the field to clear the birthday modal
		});
	}

	it('JavaScript disabled', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Disable JavaScript support
		cy.get('#mc_use_javascript').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Run validation tests
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			validateRequiredFields(url);
		});
	});

	it('JavaScript enabled', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Enable JavaScript support
		cy.get('#mc_use_javascript').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Run validation tests
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			validateRequiredFields(url);
		});
	});
});