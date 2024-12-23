/* eslint-disable no-undef */
describe('Subscribe actions', () => {
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
	});

	// TODO: This is a known bug. The back link goes to a 404 page on the Mailchimp account.
	it('after a double optin subscribe a user can click a back link to return to the website', () => {
		
	});
});