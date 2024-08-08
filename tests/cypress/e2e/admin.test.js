/* eslint-disable no-undef */
describe('Admin can login and make sure plugin is activated', () => {
	before(() => {
		cy.login();
	});

	it('Can deactivate and activate plugin?', () => {
		cy.deactivatePlugin('mailchimp');
		cy.activatePlugin('mailchimp');
	});

	it('Can see "Mailchimp" menu and Can visit "Mailchimp" settings page.', () => {
		cy.visit('/wp-admin/');

		// Check Mailchimp menu.
		cy.get('#adminmenu li#toplevel_page_mailchimp_sf_options').contains('Mailchimp');

		// Check Heading
		cy.get('#adminmenu li#toplevel_page_mailchimp_sf_options').click();
		cy.get('#wpbody .mailchimp-header h1').contains('Mailchimp List Subscribe Form');
	});
});
