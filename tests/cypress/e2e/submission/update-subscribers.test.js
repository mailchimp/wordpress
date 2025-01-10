/* eslint-disable no-undef */
// TODO: Test not written yet
describe.skip('Update Existing Subscriber?', () => {
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

	it('Update existing subscribers when they resubmit the signup form if option is checked', () => {
		// Write test...
	});
	
	it('Do not update existing subscribers when they resubmit the signup form if option is unchecked', () => {
		// Write test...
	});
});
