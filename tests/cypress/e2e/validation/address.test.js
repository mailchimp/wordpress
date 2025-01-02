/* eslint-disable no-undef */

/**
 * Test Suite for Address Field Validation
 * Includes before/after setup and testing with/without JavaScript.
 */
describe('Address Field Validation', () => {
	const validAddresses = [
		{ addr1: '123 Main St', city: 'Springfield', state: 'IL', zip: '62701', country: 'USA' },
		{ addr1: '456 Elm St', city: 'Smallville', state: 'KS', zip: '66002', country: 'USA' },
	];

	const invalidAddresses = [
		{ addr1: '', city: 'Springfield' },    // Missing Addr 1
		{ addr1: '123 Main St', city: '' },    // Missing City

		// TODO: This is disabled because only one validation error will display at a time
		// { addr1: '', city: '' },               // Both Addr 1 and City missing
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
		cy.selectList('10up'); // Ensure data is refreshed in WordPress
	});

	after(() => {
		// Cleanup: Reset address fields to optional
		cy.getListId('10up').then((listId) => {
			cy.updateMergeFieldByTag(listId, 'ADDRESS', { required: false });
		});
		cy.selectList('10up'); // Refresh list in WordPress
	});

	/**
	 * Helper Function: Generate Random Numbers
	 * Generates a random number with `x` digits.
	 */
	function randomXDigiNumber(x) {
		return Number(Array.from({ length: x }, () => Math.floor(Math.random() * 10)).join(''));
	}

	// TODO: These should be test cases
	function testAddressValidation () {
		invalidAddresses.forEach((address) => {
			[shortcodePostURL, blockPostPostURL].forEach((url) => {
				cy.visit(url);

				// Randomize email to prevent Mailchimp from blocking the submission as spam
				const randomEmail = `invalidemail${randomXDigiNumber(10)}@gmail.com`;
				
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

		validAddresses.forEach((address) => {
			[shortcodePostURL, blockPostPostURL].forEach((url) => {
				cy.visit(url);

				// Randomize email to prevent Mailchimp from blocking the submission as spam
				const randomEmail = `validemail${randomXDigiNumber(10)}@gmail.com`;

				cy.get('#mc_mv_EMAIL').type(randomEmail);
				cy.get('#mc_mv_ADDRESS-addr1').clear().type(address.addr1);
				cy.get('#mc_mv_ADDRESS-city').clear().type(address.city);
				cy.get('#mc_mv_ADDRESS-state').clear().type(address.state);
				cy.get('#mc_mv_ADDRESS-zip').type(address.zip);
				cy.get('#mc_mv_ADDRESS-country').type(address.country);
				cy.submitFormAndVerifyWPSuccess();
			});
		});
	};

	it('JavaScript disabled', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_use_javascript').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		testAddressValidation();
	});

	it('JavaScript enabled', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_use_javascript').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		testAddressValidation();
	});
});
