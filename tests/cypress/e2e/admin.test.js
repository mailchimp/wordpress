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
		cy.get('#wpbody .mailchimp-sf-header h3').contains('Mailchimp List Subscribe Form');
	});

	it('Admin can see "Create account" button and Can visit "Create account" settings page.', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Check Create account button.
		cy.get('.button.mailchimp-sf-button.button-secondary').should('be.visible');
		cy.get('.button.mailchimp-sf-button.button-secondary').contains('Create an account');

		cy.get('.button.mailchimp-sf-button.button-secondary').click();
		cy.get('.mailchimp-sf-create-account .title').contains('Confirm your information');
		cy.get('#mailchimp-sf-create-activate-account').should('be.visible');
	});

	it("Admin shouldn't able to submit create account form with invalid data", () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_create_account');

		// Submit form without filling any data.
		cy.get('#mailchimp-sf-create-activate-account').click();

		// Check error messages.
		cy.get('#mailchimp-sf-first_name-error').contains("First name can't be blank.");
		cy.get('#mailchimp-sf-last_name-error').contains("Last name can't be blank.");
		cy.get('#mailchimp-sf-email-error').contains(
			'Email confirmation must match confirmation email.',
		);
		cy.get('#mailchimp-sf-confirm_email-error').contains(
			'Email confirmation must match the field above.',
		);
		cy.get('#mailchimp-sf-address-error').contains("Address line 1 can't be blank.");
		cy.get('#mailchimp-sf-city-error').contains("City can't be blank.");
		cy.get('#mailchimp-sf-state-error').contains("State can't be blank.");
		cy.get('#mailchimp-sf-zip-error').contains("Zip can't be blank.");

		cy.get('#email').clear().type('test');
		cy.get('#confirm_email').clear().type('test');
		cy.get('#mailchimp-sf-create-activate-account').click();
		cy.get('#mailchimp-sf-email-error').contains('Insert correct email.');
	});
});
