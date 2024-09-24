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

		cy.popup().find('input#submitButton').click({ force: true });
		cy.wait(10000); // Not a best practice, but did not find a better way to handle this.

		cy.get('.mc-user h3').contains('Logged in as: ');
		cy.get('input[value="Logout"]').should('exist');
	});
});
