/**
 * Log out of Mailchimp account
 */
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