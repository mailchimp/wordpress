/* eslint-disable no-undef */
// This is failing all other tests that come after it by causing the tests to redirect to login
// This is causing some kind of error with the session data
describe('Settings data persistence', () => {
	before(() => {
		cy.setDoubleOptInOption(false);
		cy.setJavaScriptOption(true);
		cy.setSettingsOption('#mc_update_existing', false);
	});

	it('Settings and list selection remain persistent between logging out and logging back in with the same account', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Initial settings
		cy.get('#mc_double_optin').should('exist').and('not.be.checked');
		cy.get('#mc_use_javascript').should('exist').and('be.checked');
		cy.get('#mc_update_existing').should('exist').and('not.be.checked');

		// Logout
		cy.mailchimpLogout();
		// cy.logout(); // Logging out messes up the session data and fails every test after it's been called

		// Login
		// cy.login();
		cy.mailchimpLogin();

		// Settings are still the same as before
		cy.get('#mc_double_optin').should('exist').and('not.be.checked');
		cy.get('#mc_use_javascript').should('exist').and('be.checked');
		cy.get('#mc_update_existing').should('exist').and('not.be.checked');
	});
});
