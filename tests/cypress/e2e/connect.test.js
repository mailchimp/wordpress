/* eslint-disable no-undef */
describe('Admin can connect to "Mailchimp" Account', () => {
	before(() => {
		cy.login();
	});

	it('Can connect to "Mailchimp" using OAuth flow.', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Logout if already connected.
		cy.get('body').then(($body) => {
			if ($body.find('input[value="Log out"]').length > 0) {
				cy.get('input[value="Log out"]').click();
			}
		});

		// Mailchimp connection login as a command in order to be reusable
		cy.mailchimpLogin();

		// Logout exists
		cy.get('.user-profile-name').should('be.visible');
		cy.get('input[value="Log out"]').should('exist');

		// Mailchimp lists exists and has at least one audience
		cy.get('#mc_list_id').should('exist');
		cy.get('#mc_list_id').children().should('have.length.greaterThan', 1); // The " — Select A List — " default option will always be present
	});
});
