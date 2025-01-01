/**
 * Custom command to submit a form and verify that the submission was successful
 */
Cypress.Commands.add('submitFormAndVerifyWPSuccess', () => {
	// Submit the form
	cy.get('#mc_signup_submit').click();

	// Verify that the form was submitted successfully
	cy.get('.mc_success_msg').should('exist');
});

/**
 * Custom command to verify that a contact was added to a specified list in Mailchimp
 */
Cypress.Commands.add('verifyContactAddedToMailchimp', (email, listName) => {
	// Step 1: Get the list ID for the specified list name
	cy.getListId(listName).then((listId) => {
		// Step 2: Retrieve the contacts from the specified list
		cy.getContactsFromAList(listId).then((contacts) => {
			cy.log('Contacts retrieved:', contacts); // Log the contacts for debugging

			// Step 3: Verify that the contact with the provided email exists in the list
			const contactJustRegistered = contacts.find((c) => c.email_address === email);
			expect(contactJustRegistered).to.exist;
		});
	});
});