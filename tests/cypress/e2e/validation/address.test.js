/* eslint-disable no-undef */

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

	let shortcodePostURL;
	let blockPostPostURL;

	before(() => {
		// Setup: Log in and configure the form
		cy.login(); // Log into WordPress
		cy.mailchimpLoginIfNotAlreadyLoggedIn(); // Log into Mailchimp

		// Load post URLs for shortcode and block post tests
		cy.fixture('postUrls.json').then((urls) => {
			shortcodePostURL = urls.shortcodePostURL;
			blockPostPostURL = urls.blockPostPostURL;
		});

		// Set address fields (Addr 1 and City) as required
		cy.getListId('10up').then((listId) => {
			cy.updateMergeFieldByTag(listId, 'ADDRESS', { required: true });
		});
		cy.selectList('10up'); // Refresh list in WordPress
	});

	after(() => {
		// Cleanup: Reset address fields to optional
		cy.getListId('10up').then((listId) => {
			cy.updateMergeFieldByTag(listId, 'ADDRESS', { required: false });
		});
		cy.selectList('10up'); // Refresh list in WordPress
	});

	function setJavaScriptOption(enabled) {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		if (enabled) {
			cy.get('#mc_use_javascript').check();
		} else {
			cy.get('#mc_use_javascript').uncheck();
		}
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	}

	function testInvalidAddresses() {
		invalidAddresses.forEach((address) => {
			[shortcodePostURL, blockPostPostURL].forEach((url) => {
				cy.visit(url);

				const randomEmail = `invalidemail${Date.now()}@gmail.com`;
				cy.get('#mc_mv_EMAIL').type(randomEmail);

				if (address.addr1 !== '') {
					cy.get('#mc_mv_ADDRESS-addr1').clear().type(address.addr1);
				}
				if (address.city !== '') {
					cy.get('#mc_mv_ADDRESS-city').clear().type(address.city);
				}

				cy.submitFormAndVerifyError();

				if (!address.addr1) {
					cy.get('.mc_error_msg').contains('ADDRESS: Please enter a value');
				}
				if (!address.city) {
					cy.get('.mc_error_msg').contains('ADDRESS: Please enter a value');
				}
			});
		});
	}

	function testValidAddresses() {
		validAddresses.forEach((address) => {
			[shortcodePostURL, blockPostPostURL].forEach((url) => {
				cy.visit(url);

				const randomEmail = `validemail${Date.now()}@gmail.com`;
				cy.get('#mc_mv_EMAIL').type(randomEmail);
				cy.get('#mc_mv_ADDRESS-addr1').clear().type(address.addr1);
				cy.get('#mc_mv_ADDRESS-city').clear().type(address.city);
				cy.get('#mc_mv_ADDRESS-state').clear().type(address.state);
				cy.get('#mc_mv_ADDRESS-zip').type(address.zip);
				cy.get('#mc_mv_ADDRESS-country').type(address.country);
				cy.submitFormAndVerifyWPSuccess();
			});
		});
	}

	context('JavaScript Disabled', () => {
		before(() => {
			setJavaScriptOption(false);
		});

		it('Valid addresses', testValidAddresses);

		it('Invalid addresses', testInvalidAddresses);
	});

	context('JavaScript Enabled', () => {
		before(() => {
			// TODO: Not sure why we need to log in twice, but this is necessary for the test to pass
			cy.login(); // Log into WordPress
			setJavaScriptOption(true);
		});

		it('Valid addresses', testValidAddresses);

		it('Invalid addresses', testInvalidAddresses);
	});
});