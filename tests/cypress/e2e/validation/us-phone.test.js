/* eslint-disable no-undef */
import { generateRandomEmail } from '../../support/functions/utility';

/**
 * Test for Phone Number Validation
 */
describe('Phone Number Validation', () => {
	let blockPostPostURL;

	const validPhones = ['1234567890', '+1 (234) 567-890'];
	const invalidPhones = ['12345678a0', '12345!7890'];

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
		cy.get('input[name="mc_mv_PHONE"]').clear().type(phone);
	}

	it('Valid phone numbers', () => {
		cy.visit(blockPostPostURL);

		validPhones.forEach((phone) => {
			const email = generateRandomEmail('validphone');
			cy.get('input[id^="mc_mv_EMAIL"]').type(email);
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
			cy.get('input[id^="mc_mv_EMAIL"]').type(email);
			fillPhoneInputs(phone);
			cy.get('#mc_signup_submit').click();
			cy.get('input[name="mc_mv_PHONE"]:invalid')
				.invoke('prop', 'validationMessage')
				.should('equal', 'Please enter a valid phone number.');
		});
	});
});
