/* eslint-disable no-undef */
describe('Logout tests', () => {
	before(() => {
		cy.login();
	});

	it('Admin can logout', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mailchimp_sf_oauth_connect').should('not.exist');
		cy.get('input[value="Logout"]').click();

		// connect to "Mailchimp" Account button should be visible.
		cy.get('#mailchimp_sf_oauth_connect').should('exist');
	});
});
