/**
 * Custom command to submit a form and verify that the submission was successful
 */
Cypress.Commands.add('submitFormAndVerifyWPSuccess', () => {
	// Submit the form
	cy.get('.mc_signup_submit_button').click();

	// Verify that the form was submitted successfully
	cy.get('.mc_success_msg').should('exist');
});

/**
 * Submit form and verify error
 */
Cypress.Commands.add('submitFormAndVerifyError', () => {
    // Submit the form
    cy.get('.mc_signup_submit_button').click();

    // Verify that the form submission failed
    cy.get('.mc_error_msg').should('exist');
});

/**
 * Custom command to verify that a contact was added to a specified list in Mailchimp
 */
Cypress.Commands.add('verifyContactInMailchimp', (email, listName = '10up', status = null) => {
	// Step 1: Get the list ID for the specified list name
	cy.getListId(listName).then((listId) => {
		// Step 2: Retrieve the contacts from the specified list
		cy.getContactsFromAList(listId, status).then((contacts) => {
			// Log the contacts for debugging
			console.log(contacts)

			// Step 3: Verify that the contact with the provided email exists in the list
			const contact = contacts.find((c) => c.email_address === email);
			expect(contact).to.exist;
			cy.wrap(contact); // Wrap the contact to allow further chaining
		});
	});
});

Cypress.Commands.add('getContactInMailchimp', (email, listName = '10up', status = null) => {
	// Step 1: Get the list ID for the specified list name
	cy.getListId(listName).then((listId) => {
		// Step 2: Retrieve the contacts from the specified list
		cy.getContactsFromAList(listId, status).then((contacts) => {
			// Step 3: Verify that the contact with the provided email exists in the list
			const contact = contacts.find((c) => c.email_address === email);
			if (contact) {
				cy.wrap(contact); // Wrap the contact to allow further chaining
			} else {
				cy.wrap(false);
			}
		});
	});
});

/**
 * Custom command to verify that a contact's status matches the expected status.
 *
 * @param {Object} contact - The contact object to verify.
 * @param {string} status - The expected status to compare against.
 *
 * @example
 * cy.verifyContactStatus(contact, 'subscribed');
 */
Cypress.Commands.add('verifyContactStatus', (contact, status) => {
	expect(contact.status).to.equal(status);
});
