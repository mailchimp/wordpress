/* eslint-disable no-undef */
// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })

import { checkMailchimpApi } from './mailchimpApi/requests';

const state = {};

/**
 * Intercepts calls to window.open() to keep a reference to the new window
 */
Cypress.Commands.add('capturePopup', () => {
	cy.window().then((win) => {
		const { open } = win;
		cy.stub(win, 'open').callsFake((...params) => {
			// Capture the reference to the popup
			state.popup = open(...params);
			return state.popup;
		});
	});
});

/**
 * Returns a wrapped body of a captured popup
 */
Cypress.Commands.add('popup', () => {
	const popup = Cypress.$(state.popup.document);
	return cy.wrap(popup.contents().find('body'));
});

Cypress.Commands.add('mailchimpLogout', () => {
	// Logout if already connected.
	cy.get('body').then(($body) => {
		if ($body.find('input[value="Logout"]').length > 0) {
			cy.get('input[value="Logout"]').click();
		}
	});
});

/**
 * Log into Mailchimp account
 * 
 * Not sure we should put this much logic into one command, but we need
 * the Mailchimp login functionality to test settings.test.js independently
 */
Cypress.Commands.add('mailchimpLogin', (username = null, password = null) => {
	username = username ?? Cypress.env('MAILCHIMP_USERNAME');
	password = password ?? Cypress.env('MAILCHIMP_PASSWORD');
	
	cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

	// Logout if already connected.
	cy.mailchimpLogout();

	// Check Mailchimp login screen for OAuth login.
	cy.get('#mailchimp_sf_oauth_connect').should('exist');

	// Enable popup capture.
	cy.capturePopup();

	cy.get('#mailchimp_sf_oauth_connect').click();
	cy.wait(6000);

	// Accept cookie consent popup window (if present)
	cy.popup().then(($popup) => {
		const acceptButtonSelector = '#onetrust-accept-btn-handler';

		// Check if the accept button is visible and click it
		if ($popup.find(acceptButtonSelector).length > 0 && $popup.find(acceptButtonSelector).is(':visible')) {
			$popup.find(acceptButtonSelector).click();
		} else {
			cy.log('Cookie consent popup not found or not visible.');
		}
	});

	cy.popup()
		.find('input#username')
		.clear()
		.type(username, { force: true });
	cy.popup()
		.find('input#password')
		.clear()
		.type(password, { force: true });
	cy.popup().find('button[type="submit"]').click({ force: true });
	cy.wait(10000); // Not a best practice, but did not find a better way to handle this.

	// DEV NOTE: This is where 2FA would appear. You must test with an account that does not enable 2FA.

	cy.popup().find('input#submitButton').click({ force: true });
	cy.wait(10000); // Not a best practice, but did not find a better way to handle this.
});

/**
 * Adds a wrapper over the mailchimpLogin command to check if
 * a user is already logged in.
 * 
 * This is to increase testing speed
 * 
 * The name is a mouth full, but is named as such to be explicit
 */
Cypress.Commands.add('mailchimpLoginIfNotAlreadyLoggedIn', () => {
	// Log into Mailchimp account if we need to.
	cy.get('body').then(($body) => {
		const hasLogout = $body.find('input[value="Logout"]').length > 0;
		if (!hasLogout) {
			cy.mailchimpLogin();
		} else {
			cy.log('Already logged into Mailchimp account');
		}
	});
});

/**
 * Checks if MAILCHIMP_USERNAME and MAILCHIMP_PASSWORD environment variables are set.
 * Stops the test execution with an error message if either is missing.
 */
Cypress.Commands.add('checkMailchimpEnv', () => {
	const username = Cypress.env('MAILCHIMP_USERNAME');
	const password = Cypress.env('MAILCHIMP_PASSWORD');
	const apiKey = Cypress.env('MAILCHIMP_API_KEY');
	const serverPrefix = Cypress.env('MAILCHIMP_API_SERVER_PREFIX');

	if (!username || !password || !apiKey || !serverPrefix) {
		const errorMessage = `
		[ERROR] Required environment variables are missing:
		MAILCHIMP_USERNAME: ${username ? `${username.slice(0, 3)}*****${username.slice(-4)}` : 'NOT SET'}
		MAILCHIMP_PASSWORD: ${password ? 'SET' : 'NOT SET'}
		MAILCHIMP_API_KEY: ${apiKey ? `${apiKey.slice(0, 3)}*****${apiKey.slice(-4)}` : 'NOT SET'}
		MAILCHIMP_API_SERVER_PREFIX: ${serverPrefix ? `${serverPrefix}` : 'NOT SET'}

		Please set these environment variables as described in the "E2E tests" section 
		of the readme or through your CI/CD environment to proceed.
		`;

		// Log the error message and stop the test
		Cypress.log({ name: 'Env Check', message: errorMessage });
		throw new Error(errorMessage);
	}

	cy.log('Environment variables for Mailchimp are correctly set.');
});

/**
 * Mailchimp API commands
 */
Cypress.Commands.add('checkMailchimpApi', checkMailchimpApi);