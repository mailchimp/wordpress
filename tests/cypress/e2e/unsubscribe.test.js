/* eslint-disable no-undef */
describe('Unsubscribe form', () => {
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

		// Check unsubscription link
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_use_unsub_link').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	});

	after(() => {
		// Uncheck unsubscription link
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_use_unsub_link').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	})

	it('unsubscribes valid emails that were previously subscribed to a list', () => {
		const testEmail = 'mailchimp-wordpress-test@10up.com';

		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			let baseUrl;

			// Visit the mailchimp block page
			cy.visit(url);

			// Get baseUrl to use for later assertion
			cy.url().then((url) => {
				// Extract the base URL
				const urlObject = new URL(url);
				baseUrl = `${urlObject.protocol}//${urlObject.host}`;
			  });
			  

			// Assert unsubscribe link exists
			cy.get('a[href*="/unsubscribe"]').should('exist');

			// TODO: Need to delete contact before each form submission or else the test will register
			// the contact as subscribed
			// Subscribe to email (to be a valid unsubscriber)
			cy.get('#mc_mv_EMAIL').type(testEmail);
			cy.get('#mc_signup_submit').click();
			cy.get('.mc_success_msg').should('exist').contains(/success/i);

			// Visit unsubscribe link
			cy.get('a[href*="/unsubscribe"]')
				.invoke('removeAttr', 'target') // Prevent opening in new window so that Cypress can test
				.click();

			// Unsubscribe
			cy.get('#email-address').type(testEmail);
			cy.get('input[type="submit"]').click();
			cy.get('body').should('contain', 'Unsubscribe Successful');

			// Navigate back to the website
			cy.contains('a', 'return to our website').should('exist').click();
			
			// TODO: Assert that we're back on our website (this is a bug, it's broken currently)
			// cy.url().should('include', baseUrl); // TODO: Do we want to assert a specific landing page?

		});
	});
	
	it('throws an error when unsubscribing an email that was never subscribed to a list', () => {
		const testEmail = 'never-subscribed-user@10up.com';

		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			// Visit the mailchimp block page
			cy.visit(url);

			// Assert unsubscribe link exists
			cy.get('a[href*="/unsubscribe"]').should('exist');

			// Visit unsubscribe link
			cy.get('a[href*="/unsubscribe"]')
				.invoke('removeAttr', 'target') // Prevent opening in new window so that Cypress can test
				.click();

			// Unsubscribe
			cy.get('#email-address').type(testEmail);
			cy.get('input[type="submit"]').click();

			// Assert that the unsubscribe didn't work because the email isn't subscribed
			cy.get('.errorText').should('contain', 'this email is not subscribed');

		});
	});

	it.skip('does not display an unsubscribe link when the unsubscribe option is disabled', () => {

	});

	it.skip('redirects the user back to the website when the user is finished unsubscribing and clicks the back link', () => {

	});
});