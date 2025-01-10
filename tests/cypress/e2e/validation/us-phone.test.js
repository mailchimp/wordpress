/* eslint-disable no-undef */
import { generateRandomEmail } from '../../support/functions/utility';

/**
 * Test Suite for Multi-Input Phone Number Validation
 * Handles both JavaScript-enabled and disabled scenarios for length and format validation.
 */
// TODO: Skipping for now because the Mailchimp API is not changing the format to US
describe.skip('US Multi-Input Phone Number Validation', () => {
	let shortcodePostURL;
	let blockPostPostURL;

	const validPhones = [
		{ area: '123', detail1: '456', detail2: '7890' },
		{ area: '987', detail1: '654', detail2: '3210' }
	];
	const invalidPhones = [
		{ area: '123', detail1: '456', detail2: '78a0' },
		{ area: '123', detail1: '45!', detail2: '7890' }
	];
	const tooShortPhones = [
		{ area: '12', detail1: '456', detail2: '789' },
		{ area: '', detail1: '45', detail2: '7890' }
	];
	const tooLongPhones = [
		{ area: '1234', detail1: '567', detail2: '890' },
		{ area: '123', detail1: '4567', detail2: '8901' }
	];

	before(() => {
		cy.login();
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		cy.fixture('postUrls').then((urls) => {
			shortcodePostURL = urls.shortcodePostURL;
			blockPostPostURL = urls.blockPostPostURL;
		});

		cy.getListId('10up').then((listId) => {
			cy.updateMergeFieldByTag(listId, 'PHONE', { required: true, options: { phone_format: 'US' } }).then(() => {
				cy.selectList('10up');
			});
		});
	});

	after(() => {
		cy.getListId('10up').then((listId) => {
			cy.updateMergeFieldByTag(listId, 'PHONE', { required: false, options: { phone_format: 'none' } });
		});
		cy.selectList('10up');
	});

	function fillPhoneInputs(phone) {
		cy.get('#mc_mv_PHONE-area').clear().type(phone.area);
		cy.get('#mc_mv_PHONE-detail1').clear().type(phone.detail1);
		cy.get('#mc_mv_PHONE-detail2').clear().type(phone.detail2);
	}

	function testValidPhones() {
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			validPhones.forEach((phone) => {
				cy.visit(url);

				const email = generateRandomEmail('validphone');
				cy.get('#mc_mv_EMAIL').type(email);
				fillPhoneInputs(phone);
				cy.submitFormAndVerifyWPSuccess();

				// Delete contact to clean up
				cy.deleteContactFrom10UpList(email);
			});
		});
	}

	function testInvalidPhones() {
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			invalidPhones.forEach((phone) => {
				cy.visit(url);

				const email = generateRandomEmail('invalidphone');
				cy.get('#mc_mv_EMAIL').type(email);
				fillPhoneInputs(phone);
				cy.submitFormAndVerifyError();
				cy.get('.mc_error_msg').contains('must consist of only numbers');
			});
		});
	}

	function testPhoneLengthValidation() {
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			tooShortPhones.forEach((phone) => {
				cy.visit(url);

				const email = generateRandomEmail('shortphone');
				cy.get('#mc_mv_EMAIL').type(email);
				fillPhoneInputs(phone);
				cy.submitFormAndVerifyError();
				cy.get('.mc_error_msg').contains('Phone number is too short');
			});

			tooLongPhones.forEach((phone) => {
				cy.visit(url);

				const email = generateRandomEmail('longphone');
				cy.get('#mc_mv_EMAIL').type(email);
				fillPhoneInputs(phone);
				cy.submitFormAndVerifyError();
				cy.get('.mc_error_msg').contains('Phone number is too long');
			});
		});
	}

	context('JavaScript Disabled', () => {
		before(() => {
			cy.setJavaScriptOption(false);
		});

		it('Valid phone numbers', testValidPhones);

		it('Invalid phone numbers', testInvalidPhones);

		it('Phone length validation', testPhoneLengthValidation);
	});

	context('JavaScript Enabled', () => {
		before(() => {
			cy.login();
			cy.setJavaScriptOption(true);
		});

		it('Valid phone numbers', testValidPhones);

		it('Invalid phone numbers', testInvalidPhones);

		it('Phone length validation', testPhoneLengthValidation);
	});
});
