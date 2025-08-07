/* eslint-disable no-undef */
import { generateRandomEmail } from '../../support/functions/utility';

/**
 * Test Suite for Address Field Validation
 * Includes setup and testing for both JavaScript-enabled and disabled states.
 */
describe('Address Field Validation', () => {
	const validAddresses = [
		{ addr1: '123 Main St', city: 'Springfield', state: 'IL', zip: '62701', country: 'USA' },
		{ addr1: '456 Elm St', city: 'Smallville', state: 'KS', zip: '66002', country: 'USA' },
	];

	const invalidAddresses = [
		{ addr1: '', city: 'Springfield' }, // Missing Addr 1
		{ addr1: '123 Main St', city: '' }, // Missing City
	];

	let blockPostPostURL;

	before(() => {
		// Setup: Log in and configure the form
		cy.login(); // Log into WordPress
		cy.mailchimpLoginIfNotAlreadyLoggedIn(); // Log into Mailchimp

		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then((urls) => {
			({ blockPostPostURL } = urls);
		});

		// Set address fields (Addr 1 and City) as required
		cy.getListId('10up').then((listId) => {
			cy.updateMergeFieldByTag(listId, 'ADDRESS', { required: true }).then(() => {
				cy.selectList('10up', true); // Refresh list in WordPress
			});
		});
	});

	after(() => {
		// Cleanup: Reset address fields to optional
		cy.getListId('10up').then((listId) => {
			cy.updateMergeFieldByTag(listId, 'ADDRESS', { required: false }).then(() => {
				cy.selectList('10up', true); // Refresh list in WordPress
			});
		});
	});

	it('Valid addresses submit', () => {
		validAddresses.forEach((address) => {
			cy.visit(blockPostPostURL);

			const email = generateRandomEmail('validemail');
			cy.get('#mc_mv_EMAIL').type(email);
			cy.get('#mc_mv_ADDRESS-addr1').clear().type(address.addr1);
			cy.get('#mc_mv_ADDRESS-city').clear().type(address.city);
			cy.get('#mc_mv_ADDRESS-state').clear().type(address.state);
			cy.get('#mc_mv_ADDRESS-zip').type(address.zip);
			cy.get('#mc_mv_ADDRESS-country').type(address.country);
			cy.submitFormAndVerifyWPSuccess();

			// Delete contact to clean up
			cy.deleteContactFromList(email);
		});
	});

	it('Invalid addresses fail validation and display error message', () => {
		invalidAddresses.forEach((address) => {
			cy.visit(blockPostPostURL);

			const email = generateRandomEmail('invalidemail');
			cy.get('#mc_mv_EMAIL').type(email);

			if (address.addr1 !== '') {
				cy.get('#mc_mv_ADDRESS-addr1').clear().type(address.addr1);
			}
			if (address.city !== '') {
				cy.get('#mc_mv_ADDRESS-city').clear().type(address.city);
			}

			cy.submitFormAndVerifyError();

			if (!address.addr1) {
				cy.get('.mc_error_msg').contains('Address: Please enter a complete address');
			}
			if (!address.city) {
				cy.get('.mc_error_msg').contains('Address: Please enter a complete address');
			}
		});
	});
});
