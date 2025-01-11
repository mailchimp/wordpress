/* eslint-disable no-undef */
describe('Settings data persistence', () => {
	it('Settings and list selection remain persistent between logging out and logging back in with the same account', () => {
        cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

        // Logout
        cy.mailchimpLogout();
        cy.logout();

        // Login
        cy.login();
        cy.mailchimpLogin();

        // Assertions regarding settings here...
	});
});
