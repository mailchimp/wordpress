/* eslint-disable no-undef */
import { generateRandomEmail } from '../../support/functions/utility';

/**
 * Test for Phone Number Validation
 */
describe('US Multi-Input Phone Number Validation', () => {
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
		{ area: '1', detail1: '45', detail2: '7890' },
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
	});

	after(() => {
		cy.getListId('10up').then((listId) => {
			cy.updateMergeFieldByTag(listId, 'PHONE', {
				required: false,
				options: { phone_format: 'none' },
			}).then(() => {
				cy.selectList('10up');
			});
		});
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
			cy.get('.mc_error_msg').contains('should be 10 digits long');
		});

		tooLongPhones.forEach((phone) => {
			const email = generateRandomEmail('longphone');
			cy.get('#mc_mv_EMAIL').type(email);
			fillPhoneInputs(phone);
			cy.submitFormAndVerifyError();
			cy.get('.mc_error_msg').contains('should be 10 digits long');
		});
	});
});
