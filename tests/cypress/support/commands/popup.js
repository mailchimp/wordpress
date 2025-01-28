// Initialize state to store the popup reference
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