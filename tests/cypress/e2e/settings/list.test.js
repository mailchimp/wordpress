/* eslint-disable no-undef */
describe('Mailchimp lists ', () => {
	let shortcodePostURL;
	let blockPostPostURL;

	before(() => {
		// TODO: Initialize tests from a blank state
		// TODO: Wipe WP data related to a users options
		// TODO: Delete all contacts in a users Mailchimp account
		// TODO: Ensure the default audience list is "10up"
		// TODO: Include all merge fields as "Visible" in the users Mailchimp account

		// Load the post URLs from the JSON file
		cy.fixture('postUrls.json').then((urls) => {
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

	it('Admin that has never saved a list can not see the form on the front end', () => {
		// Step 1: Log the user out of the current account (if logged in)
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.mailchimpLogout();
	
		// Verify the user is logged out
		cy.get('#mailchimp_sf_oauth_connect').should('exist');
	
		// Step 2: Log in with a test user account that has never saved a list
		cy.mailchimpLogin(Cypress.env('MAILCHIMP_USERNAME_NO_LIST'), Cypress.env('MAILCHIMP_PASSWORD_NO_LIST'));
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
	
		// Step 3: Verify no list is saved for the test user
		cy.get('#mc_list_id').should('have.value', ''); // Assuming empty value indicates no list is saved

        // Step 4: Verify there is no settings form if no list is saved
        // If there are no Update Subscribe Form Settings buttons then we can assume no settings form is visible
        cy.get('input[value="Update Subscribe Form Settings"]').should('not.exist');
	
		// Step 5: Verify the signup form is not displayed on the frontend
		cy.visit('/'); // Navigate to the frontend homepage
		cy.get('#mc_signup').should('not.exist'); // Ensure the form does not exist

		// Clean up
		cy.mailchimpLogout();
		cy.mailchimpLogin();
	});
});
