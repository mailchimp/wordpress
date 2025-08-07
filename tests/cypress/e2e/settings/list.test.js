/* eslint-disable no-undef */
describe('Mailchimp lists ', () => {
	let shortcodePostURL;
	let blockPostPostURL;

	before(() => {
		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then((urls) => {
			({ shortcodePostURL, blockPostPostURL } = urls);
		});

		cy.login(); // WP
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		// Call mailchimpLists once and store the result in the alias 'mailchimpLists'
		cy.getMailchimpLists().then((mailchimpLists) => {
			Cypress.env('mailchimpLists', mailchimpLists); // Save globally
		});
	});

	it("All lists from user's account populate the WP admin dropdown list", () => {
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
		cy.selectList('10up');

		// Verify that the settings are visible if a list is saved
		cy.get('h2.mailchimp-sf-settings-table-title')
			.first()
			.contains('Form copy')
			.should('be.visible');
	});

	it('Admin that has never saved a list can not see the form on the front end', () => {
		cy.wpCli('wp option delete mc_list_id').then(() => {
			cy.visit(shortcodePostURL);
			cy.get('#mc_signup_form').should('not.exist');

			cy.visit(blockPostPostURL);
			cy.get('#mc_signup_form').should('not.exist');

			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
			cy.selectList('10up');
		});
	});
});
