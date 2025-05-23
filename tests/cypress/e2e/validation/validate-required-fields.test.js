/* eslint-disable no-undef */
import { generateRandomEmail } from '../../support/functions/utility';

describe('Validate required fields', () => {
	let blockPostPostURL;
	const email = generateRandomEmail('testemail-neversubmitted');

	// (almost) the same in the WP admin as on the FE
	const requiredFields = [
		{ selector: '#mc_mv_FNAME', errorMessage: 'First Name:', input: 'Test' },
		{ selector: '#mc_mv_LNAME', errorMessage: 'Last Name:', input: 'User' },
		{ selector: '#mc_mv_ADDRESS-addr1', errorMessage: 'Address:', input: '123 Fake St.' }, // Address has sub fields on the FE form
		{ selector: '#mc_mv_ADDRESS-city', errorMessage: 'Address:', input: 'Nashville' }, // Address has sub fields on the FE form
		{ selector: '#mc_mv_ADDRESS-state', errorMessage: 'Address:', input: 'TN' }, // Address has sub fields on the FE form
		{ selector: '#mc_mv_ADDRESS-zip', errorMessage: 'Address:', input: '12345' }, // Address has sub fields on the FE form
		{ selector: '#mc_mv_BIRTHDAY', errorMessage: 'Birthday:', input: '01/10' },
		{ selector: '#mc_mv_COMPANY', errorMessage: 'Company:', input: '10up' },
		{ selector: '#mc_mv_PHONE', errorMessage: 'Phone Number:', input: '555-555-5555' },
		{ selector: '#mc_mv_MMERGE8', errorMessage: 'Date:', input: '01/01/2030' },
		{ selector: '#mc_mv_MMERGE9', errorMessage: 'Zip Code:', input: '12345' },
		{ selector: '#mc_mv_MMERGE10', errorMessage: 'Website:', input: 'https://10up.com' },
		{
			selector: '#mc_mv_MMERGE11',
			errorMessage: 'Image:',
			input: 'https://10up.com/wp-content/themes/10up-sept2016/assets/img/icon-strategy.png',
		},
	];

	const requiredSelectFields = [
		// Country is selected by default so no need to test this validation
		// { selector: '#mc_mv_ADDRESS-country', errorMessage: 'Address:', input: 'USA' }, // Address has sub fields on the FE form
		{ selector: '#mc_mv_MMERGE7', errorMessage: 'Choose one:', input: 'First Choice' },
	];

	const requiredCheckboxFields = [
		{ selector: '#mc_mv_MMERGE6_0', errorMessage: 'Choose one:', input: 'First Choice' },
	];

	before(() => {
		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then(({ blockPostPostURL: url }) => {
			blockPostPostURL = url;
		});

		cy.login(); // WordPress login
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		// Set all merge fields to required in the Mailchimp test user account
		cy.setMergeFieldsRequired(true, '10up', [
			'FNAME',
			'LNAME',
			'ADDRESS',
			'BIRTHDAY',
			'COMPANY',
			'MMERGE8',
			'MMERGE9',
			'MMERGE10',
			'MMERGE11',
			'MMERGE7',
			'MMERGE6',
			'PHONE',
		]);
	});

	after(() => {
		// I don't know why we need to login again, but we do
		cy.login(); // WordPress login

		// Cleanup: Set all merge fields to not required in the Mailchimp test user account
		cy.setMergeFieldsRequired(false, '10up', [
			'FNAME',
			'LNAME',
			'ADDRESS',
			'BIRTHDAY',
			'COMPANY',
			'MMERGE8',
			'MMERGE9',
			'MMERGE10',
			'MMERGE11',
			'MMERGE7',
			'MMERGE6',
			'PHONE',
		]);

		// Cleanup: Uncheck all optional merge fields
		cy.toggleMergeFields('uncheck');
	});

	// TODO: Validation errors clear the entire form. We should fix this.
	// We could also significantly reduce the time this test takes by fixing this bug.
	function fillOutAllFields() {
		cy.get('#mc_mv_EMAIL').clear().type(email); // Email is always required

		requiredFields.forEach((field) => {
			if (field.selector === '#mc_mv_PHONE') {
				const phone = field.input.split('-');
				cy.get('#mc_mv_PHONE-area').clear().type(phone[0]);
				cy.get('#mc_mv_PHONE-detail1').clear().type(phone[1]);
				cy.get('#mc_mv_PHONE-detail2').clear().type(phone[2]);
			} else {
				cy.get(field.selector).clear().type(field.input);
			}
			cy.get('body').click(0, 0); // Click outside the field to clear the datepicker modal
		});

		requiredSelectFields.forEach((field) => {
			cy.get(field.selector).select(field.input);
			cy.get('body').click(0, 0); // Click outside the field to clear the datepicker modal
		});

		requiredCheckboxFields.forEach((field) => {
			cy.get(field.selector).check();
			cy.get('body').click(0, 0); // Click outside the field to clear the datepicker modal
		});
	}

	// TODO: Test just takes too long to run
	it('ensures that a required field can not be empty', () => {
		cy.visit(blockPostPostURL);

		// Ensure the form exists
		cy.get('#mc_signup').should('exist');

		// Fill out entire form everytime so we can narrow tests to one input at a time
		fillOutAllFields();

		// Test validation for each required field
		requiredFields.forEach((field) => {
			// Submit the form without input to trigger validation
			if (field.selector === '#mc_mv_PHONE') {
				cy.get('#mc_mv_PHONE-area').clear();
				cy.get('#mc_mv_PHONE-detail1').clear();
				cy.get('#mc_mv_PHONE-detail2').clear();
			} else {
				cy.get(field.selector).clear(); // Ensure field is empty
			}
			cy.get('body').click(0, 0); // Click outside the field to clear the datepicker modal
			cy.get('#mc_signup_submit').click();

			// Assert the error message is displayed
			cy.get('.mc_error_msg').should('exist');
			cy.get('.mc_error_msg').should('include.text', field.errorMessage);

			// Fill in the field
			if (field.selector === '#mc_mv_PHONE') {
				const phone = field.input.split('-');
				cy.get('#mc_mv_PHONE-area').clear().type(phone[0]);
				cy.get('#mc_mv_PHONE-detail1').clear().type(phone[1]);
				cy.get('#mc_mv_PHONE-detail2').clear().type(phone[2]);
			} else {
				cy.get(field.selector).type(field.input);
			}
			cy.get('body').click(0, 0); // Click outside the field to clear the datepicker modal
		});
	});
});
