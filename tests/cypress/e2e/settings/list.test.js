/* eslint-disable no-undef */
describe('Mailchimp lists ', () => {
	let shortcodePostURL;
	let blockPostPostURL;

	before(() => {
		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then((urls) => {
			shortcodePostURL = urls.shortcodePostURL;
			blockPostPostURL = urls.blockPostPostURL;
		});

		cy.login(); // WP
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		// Call mailchimpLists once and store the result in the alias 'mailchimpLists'
		cy.getMailchimpLists().then((mailchimpLists) => {
			Cypress.env('mailchimpLists', mailchimpLists); // Save globally
		});
	});

	it('All lists from user\'s account populate the WP admin dropdown list', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		const $wpLists = cy.get('#mc_list_id > option[value]:not([value=""])'); // Lists from the WP admin dropdown
		const mailchimpLists = Cypress.env('mailchimpLists');

		// Verify that the same number of lists exist in the dropdown as in the Mailchimp account
		$wpLists.should('have.length', mailchimpLists.length);

		mailchimpLists.forEach((list) => {
			// Verify that all Mailchimp account lists exist in dropdown
			cy.get('#mc_list_id').should('contain', list.name);
		});
	});

	it('Admin can see list and save it', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Verify that list can be saved
		cy.get('.mc-h2').contains('Your Lists');
		cy.selectList('10up');
		cy.get('#mc-message .success_msg b').contains('Success!');

        // Verify that the settings are visible if a list is saved
        cy.get('input[value="Update Subscribe Form Settings"]').should('exist');
	});

	// This test has been decided to be skipped and marked as a "doing it wrong" site owner scenario
	// We are not worried about this testing scenario
	it.skip('Admin that has never saved a list can not see the form on the front end', () => {});
});
