// ***********************************************************
// This example support/e2e.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

import '@10up/cypress-wp-utils';
import './commands';

// TODO: Initialize tests from a blank state
// TODO: Wipe WP data related to a users options
// TODO: Delete all contacts in a users Mailchimp account
// TODO: Include all merge fields as "Visible" in the users Mailchimp account
before(() => {
	// Add global setup logic here
	cy.checkMailchimpEnv(); // Example: Check environment variables
	cy.checkMailchimpApi(); // Throw error if we can't connect to the API
	cy.log('Global setup completed!');

	// Default settings for tests
	cy.login(); // WP
	cy.mailchimpLoginIfNotAlreadyLoggedIn();

	cy.selectList('10up');

	cy.setDoubleOptInOption(false);
	cy.setSettingsOption('#mc_update_existing', false);

	// Merge fields
	cy.setMergeFieldsRequired(false); // No merge fields are required
	cy.toggleMergeFields('uncheck'); // Start without merge fields

	cy.log('Default testing options set!');
});

beforeEach(() => {
	cy.session('login', cy.login, {
		cacheAcrossSpecs: true,
	});
});
