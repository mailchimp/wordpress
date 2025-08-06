/* eslint-disable no-undef */
describe('Logout tests', () => {
	before(() => {
		cy.login();
	});

	it('Admin can logout', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mailchimp_sf_oauth_connect').should('not.exist');

		cy.on('window:confirm', (text) => {
			expect(text).to.contains('Are you sure you want to log out?');
			return true;
		});

		cy.get('input[value="Log out"]').click();

		// connect to "Mailchimp" Account button should be visible.
		cy.get('#mailchimp_sf_oauth_connect').should('exist');
	});
});
