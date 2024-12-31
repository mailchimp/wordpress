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

// Import commands.js using ES2015 syntax:
import './commands';
import { checkMailchimpApi } from './mailchimpApi/requests';

before(() => {
	// Add global setup logic here
	cy.checkMailchimpEnv(); // Example: Check environment variables
	checkMailchimpApi(); // Throw error if we can't connect to the API
	cy.log('Global setup completed!');
});

beforeEach( () => {
	cy.session( 'login', cy.login, {
		cacheAcrossSpecs: true,
	} );
} );
