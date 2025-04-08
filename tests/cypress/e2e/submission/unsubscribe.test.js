/* eslint-disable no-undef */
import { generateRandomEmail } from '../../support/functions/utility';

describe('Unsubscribe form', () => {
	let shortcodePostURL;
	let blockPostPostURL;

	before(() => {
		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then((urls) => {
			({ shortcodePostURL, blockPostPostURL } = urls);
		});

		cy.login(); // WP
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		cy.selectList('10up'); // Ensure list is selected, refreshes Mailchimp data with WP

		// Single Opt-in
		cy.setDoubleOptInOption(false);

		// Check unsubscription link
		cy.setSettingsOption('#mc_use_unsub_link', true);
	});

	after(() => {
		// I don't know why we have to login again, but we do
		cy.login(); // WP

		// Uncheck unsubscription link
		cy.setSettingsOption('#mc_use_unsub_link', false);
	});

	it('unsubscribe link appears on both shortcode and block pages', () => {
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			// Visit the mailchimp block page
			cy.visit(url);

			// Assert unsubscribe link exists
			cy.get('a[href*="/unsubscribe"]').should('exist');
		});
	});

	it('unsubscribes valid emails that were previously subscribed to a list', () => {
		const email = generateRandomEmail('previously-subscribed-email');

		// Subscribe email to setup test
		cy.subscribeToListByName(email);

		let baseUrl;

		// Visit the mailchimp block page
		cy.visit(blockPostPostURL);

		// Get baseUrl to use for later assertion
		cy.url().then((url) => {
			// Extract the base URL
			const urlObject = new URL(url);
			baseUrl = `${urlObject.protocol}//${urlObject.host}`;
		});

		// Assert unsubscribe link exists
		cy.get('a[href*="/unsubscribe"]').should('exist');

		// Visit unsubscribe link
		cy.get('a[href*="/unsubscribe"]')
			.invoke('removeAttr', 'target') // Prevent opening in new window so that Cypress can test
			.click();

		// Unsubscribe
		cy.get('#email-address').type(email);
		cy.get('input[type="submit"]').click();
		cy.get('body').should('contain', 'Unsubscribe Successful');

		// Navigate back to the website button exists
		cy.contains('a', 'return to our website').should('exist');

		// Verify contact exists in Mailchimp with status 'unsubscribed'
		cy.verifyContactInMailchimp(email, '10up', 'unsubscribed').then((contact) => {
			cy.verifyContactStatus(contact, 'unsubscribed');

			// Delete contact to clean up
			cy.deleteContactFromList(email);
		});

		// Navigate to back website
		// NOTE: The website URL is site in Mailchimp and it won't accept localhost or our test URL
		// TODO: Assert that we're back on our website (we currently have no way to set this)
		// cy.contains('a', 'return to our website').click();
		// cy.url().should('include', baseUrl); // TODO: Do we want to assert a specific landing page?
	});

	it('throws an error when unsubscribing an email that was never subscribed to a list', () => {
		const email = generateRandomEmail('never-subscribed-user');

		// Visit the mailchimp block page
		cy.visit(blockPostPostURL);

		// Assert unsubscribe link exists
		cy.get('a[href*="/unsubscribe"]').should('exist');

		// Visit unsubscribe link
		cy.get('a[href*="/unsubscribe"]')
			.invoke('removeAttr', 'target') // Prevent opening in new window so that Cypress can test
			.click();

		// Unsubscribe
		cy.get('#email-address').type(email);
		cy.get('input[type="submit"]').click();

		// Assert that the unsubscribe didn't work because the email isn't subscribed
		cy.get('.errorText').should('contain', 'this email is not subscribed');
	});

	it('does not display an unsubscribe link when the unsubscribe option is disabled', () => {
		// Not sure why we have to login for this test, but we do
		cy.login(); // WP

		// Uncheck unsubscription link
		cy.setSettingsOption('#mc_use_unsub_link', false);

		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			// Visit the mailchimp block page
			cy.visit(url);

			// Assert unsubscribe link exists
			cy.get('a[href*="/unsubscribe"]').should('not.exist');
		});
	});

	// NOTE: We can not set the "return to website" URL from the Mailchimp plugin or through the API.
	// Alternative proposals on issue #91 and #92 to add a user tutorial
	// it.skip('redirects the user back to the website when the user is finished unsubscribing and clicks the back link', () => {});
});
