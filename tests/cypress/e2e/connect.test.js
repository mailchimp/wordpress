/* eslint-disable no-undef */
describe('Admin can connect to "Mailchimp" Account', () => {
	before(() => {
		cy.login();
	});

	it('Can connect to "Mailchimp" using OAuth flow.', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Logout if already connected.
		cy.get('body').then(($body) => {
			if ($body.find('input[value="Logout"]').length > 0) {
				cy.get('input[value="Logout"]').click();
			}
		});

		// Check Mailchimp menu.
		cy.get('#mailchimp_sf_oauth_connect').should('exist');

		// Enable popup capture.
		cy.capturePopup();

		cy.get('#mailchimp_sf_oauth_connect').click();
		cy.wait(6000);

		// Accept cookie consent popup window (if present)
		cy.popup().then(($popup) => {
			const acceptButtonSelector = '#onetrust-accept-btn-handler';

			// Check if the accept button is visible and click it
			if ($popup.find(acceptButtonSelector).length > 0 && $popup.find(acceptButtonSelector).is(':visible')) {
				$popup.find(acceptButtonSelector).click();
			} else {
				cy.log('Cookie consent popup not found or not visible.');
			}
		});

		cy.popup()
			.find('input#username')
			.clear()
			.type(Cypress.env('MAILCHIMP_USERNAME'), { force: true });
		cy.popup()
			.find('input#password')
			.clear()
			.type(Cypress.env('MAILCHIMP_PASSWORD'), { force: true });
		cy.popup().find('button[type="submit"]').click({ force: true });
		cy.wait(10000); // Not a best practice, but did not find a better way to handle this.

		// DEV NOTE: This is where 2FA would appear. You must test with an account that does not enable 2FA.

		cy.popup().find('input#submitButton').click({ force: true });
		cy.wait(10000); // Not a best practice, but did not find a better way to handle this.

		cy.get('.mc-user h3').contains('Logged in as: ');
		cy.get('input[value="Logout"]').should('exist');
	});
});
