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
