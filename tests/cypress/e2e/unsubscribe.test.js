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
		// Visit the mailchimp block page
		// Subscribe to email (to be a valid unsubscriber)
		// Assert unsubscribe link exists
		// Visit unsubscribe link
		// Unsubscribe
		// Select a reason
		// Navigate back to the website (this is a bug, it's broken currently)
	});
	
	it('throws an error when unsubscribing an email that was never subscribed to a list', () => {
		// Visit the mailchimp block page
		// Assert unsubscribe link exists
		// Visit unsubscribe link
		// Unsubscribe
		// Assert that the unsubscribe didn't work because the email isn't subscribed
	});

	it('does not display an unsubscrie link when the unsubscribe option is disabled', () => {

	});

	it('redirects the user back to the website when the user is finished unsubscribing and clicks the back link', () => {

	});
});