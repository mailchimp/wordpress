/* eslint-disable no-undef */
describe.skip('Resubscribe actions', () => {
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

	// TODO: This is an enhancement idea for the future
	// Mailchimp does not allow unsubscribed contacts to be resubscribed through the API
	it.skip('Subscribers who have previously unsubscribed should receive a link to the Mailchimp self hosted sign up form', () => {
		// Write test...
	});
});