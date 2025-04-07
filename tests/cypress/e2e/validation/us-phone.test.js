/* eslint-disable no-undef */
import { generateRandomEmail } from '../../support/functions/utility';

/**
 * Test Suite for Multi-Input Phone Number Validation
 * Handles both JavaScript-enabled and disabled scenarios for length and format validation.
 */
// TODO: BUG: Skipping for now because when a US phone number is selected in the Mailchimp account, but
// not present on the webform there will always be a fatal error. There is a fix pending for 1.7.0.
// TODO: Skipping for now because the Mailchimp API does not allow changing the format for a phone merge
// field to the US style
describe.skip('US Multi-Input Phone Number Validation', () => {
	let blockPostPostURL;

	const validPhones = [
		{ area: '123', detail1: '456', detail2: '7890' },
		{ area: '987', detail1: '654', detail2: '3210' },
	];
	const invalidPhones = [
		{ area: '123', detail1: '456', detail2: '78a0' },
		{ area: '123', detail1: '45!', detail2: '7890' },
	];
	const tooShortPhones = [
		{ area: '12', detail1: '456', detail2: '789' },
		{ area: '', detail1: '45', detail2: '7890' },
	];
	const tooLongPhones = [
		{ area: '1234', detail1: '567', detail2: '890' },
		{ area: '123', detail1: '4567', detail2: '8901' },
	];

	before(() => {
		cy.login();
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		cy.fixture('postUrls').then((urls) => {
			({ blockPostPostURL } = urls);
		});

		cy.getListId('10up').then((listId) => {
			cy.updateMergeFieldByTag(listId, 'PHONE', {
				required: true,
				options: { phone_format: 'US' },
			}).then(() => {
				cy.selectList('10up');
			});
		});

		// Test validation without JS to ensure error handling mechanism for all scenarios
		cy.setJavaScriptOption(false);
	});

	after(() => {
		cy.getListId('10up').then((listId) => {
			cy.updateMergeFieldByTag(listId, 'PHONE', {
				required: false,
				options: { phone_format: 'none' },
			});
		});
		cy.selectList('10up');
		cy.setJavaScriptOption(true);
	});

	function fillPhoneInputs(phone) {
		cy.get('#mc_mv_PHONE-area').clear().type(phone.area);
		cy.get('#mc_mv_PHONE-detail1').clear().type(phone.detail1);
		cy.get('#mc_mv_PHONE-detail2').clear().type(phone.detail2);
	}

	it('Valid phone numbers', () => {
		cy.visit(blockPostPostURL);

		validPhones.forEach((phone) => {
			const email = generateRandomEmail('validphone');
			cy.get('#mc_mv_EMAIL').type(email);
			fillPhoneInputs(phone);
			cy.submitFormAndVerifyWPSuccess();

			// Delete contact to clean up
			cy.deleteContactFromList(email);
		});
	});

	it('Invalid phone numbers', () => {
		cy.visit(blockPostPostURL);

		invalidPhones.forEach((phone) => {
			const email = generateRandomEmail('invalidphone');
			cy.get('#mc_mv_EMAIL').type(email);
			fillPhoneInputs(phone);
			cy.submitFormAndVerifyError();
			cy.get('.mc_error_msg').contains('must consist of only numbers');
		});
	});

	it('Phone length validation', () => {
		cy.visit(blockPostPostURL);

		tooShortPhones.forEach((phone) => {
			const email = generateRandomEmail('shortphone');
			cy.get('#mc_mv_EMAIL').type(email);
			fillPhoneInputs(phone);
			cy.submitFormAndVerifyError();
			cy.get('.mc_error_msg').contains('Phone number is too short');
		});

		tooLongPhones.forEach((phone) => {
			const email = generateRandomEmail('longphone');
			cy.get('#mc_mv_EMAIL').type(email);
			fillPhoneInputs(phone);
			cy.submitFormAndVerifyError();
			cy.get('.mc_error_msg').contains('Phone number is too long');
		});
	});
});
