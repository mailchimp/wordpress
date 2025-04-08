/* eslint-disable no-undef */
// TODO: BLOCKED - Need access to a service that can catch test emails so we can finish the email verification process.
describe.skip('Double Opt-in Subscriptions', () => {
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

		cy.selectList('10up'); // Ensure list is selected, refreshes Mailchimp data with WP

		cy.setDoubleOptInOption(false);
	});

	it.skip('When double opt-in is enabled a subscriber must verify their email before their submission displays in Mailchimp', () => {
		// Write test...
	});

	// TODO: This is a known bug. The back link goes to a 404 page on the Mailchimp account.
	it.skip('after a double optin subscribe a user can click a back link to return to the website', () => {
		// Write test...
	});
});
