/* eslint-disable no-undef */
import { generateRandomEmail } from '../../support/functions/utility';

describe('Unsubscribe form', () => {
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

		// Check unsubscription link
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_use_unsub_link').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	});

	after(() => {
		// I don't know why we have to login again, but we do
		cy.login(); // WP

		// Uncheck unsubscription link
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_use_unsub_link').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	})

	it('unsubscribes valid emails that were previously subscribed to a list', () => {
		const email = generateRandomEmail('previously-subscribed-email');

		// Subscribe email to setup test
		cy.getListId('10up').then((listId) => {
			cy.subscribeToList(listId, email);
		});

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

			// Visit unsubscribe link
			cy.get('a[href*="/unsubscribe"]')
				.invoke('removeAttr', 'target') // Prevent opening in new window so that Cypress can test
				.click();

			// Unsubscribe
			cy.get('#email-address').type(email);
			cy.get('input[type="submit"]').click();
			cy.get('body').should('contain', 'Unsubscribe Successful');

			// Navigate back to the website button exists
			cy.contains('a', 'return to our website')
				.should('exist');

			// Delete contact to clean up
			cy.deleteContactFrom10UpList(email);
			
			// Navigate to website
			// NOTE: The website URL is site in Mailchimp and it won't accept localhost or our test URL
			// TODO: Assert that we're back on our website (we currently have no way to set this)
			// cy.contains('a', 'return to our website').click();
			// cy.url().should('include', baseUrl); // TODO: Do we want to assert a specific landing page?

		});
	});
	
	it('throws an error when unsubscribing an email that was never subscribed to a list', () => {
		const email = generateRandomEmail('never-subscribed-user');

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
			cy.get('#email-address').type(email);
			cy.get('input[type="submit"]').click();

			// Assert that the unsubscribe didn't work because the email isn't subscribed
			cy.get('.errorText').should('contain', 'this email is not subscribed');

		});
	});

	it.skip('does not display an unsubscribe link when the unsubscribe option is disabled', () => {
		// Test here...
	});

	it.skip('redirects the user back to the website when the user is finished unsubscribing and clicks the back link', () => {
		// Test here...
	});
});