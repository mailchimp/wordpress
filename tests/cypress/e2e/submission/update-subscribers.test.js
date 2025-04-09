/* eslint-disable no-undef */
describe('Update Existing Subscriber?', () => {
	let blockPostPostURL;
	const email = 'existing-subscriber@10up.com'; // Static email, exists in test account already

	before(() => {
		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then((urls) => {
			({ blockPostPostURL } = urls);
		});

		cy.login(); // WP
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		cy.setDoubleOptInOption(false);

		// Set up for "Update Existing Subscribers?"
		cy.setSettingsOption('#mc_update_existing', true);
		cy.setMergeFieldsRequired(false); // No required merge fields (selects 10up list too)
		cy.toggleMergeFields('check'); // All merge fields are included

		// If subscribed contact doesn't exist then create it
		cy.getContactFromList(email).then((contact) => {
			if (!contact) {
				cy.subscribeToListByName(email);
				console.log(`Creating ${email}`);
			} else {
				console.log(`${email} exists`);
			}
		});
	});

	function generateRandomString(length = 10) {
		return Math.random()
			.toString(36)
			.substring(2, 2 + length);
	}

	it('Update existing subscribers when they resubmit the signup form if option is checked', () => {
		// Navigate to the shortcode post
		cy.visit(blockPostPostURL);

		// Generate random strings
		const firstName = generateRandomString();
		const lastName = generateRandomString();

		// Fill the form and submit it
		cy.get('#mc_mv_EMAIL').clear().type(email);
		cy.get('#mc_mv_FNAME').clear().type(firstName);
		cy.get('#mc_mv_LNAME').clear().type(lastName);

		// Submit and assert success
		cy.submitFormAndVerifyWPSuccess();

		// Verify subscriber data is updated in Mailchimp
		cy.verifyContactInMailchimp(email).then((contact) => {
			cy.verifyContactStatus(contact, 'subscribed');
			expect(contact.merge_fields.FNAME).to.equal(firstName);
			expect(contact.merge_fields.LNAME).to.equal(lastName);
		});
	});

	it('Verify that existing subscriber data is updated accurately without creating duplicate records', () => {
		// Navigate to the shortcode post
		cy.visit(blockPostPostURL);

		// Generate random strings
		const firstName = generateRandomString();
		const lastName = generateRandomString();

		// Fill the form and submit it
		cy.get('#mc_mv_EMAIL').clear().type(email);
		cy.get('#mc_mv_FNAME').clear().type(firstName);
		cy.get('#mc_mv_LNAME').clear().type(lastName);

		// Submit and assert success
		cy.submitFormAndVerifyWPSuccess();

		// Verify a duplicate contact has not been created
		cy.getListId('10up').then((listId) => {
			cy.getContactsFromAList(listId).then((contacts) => {
				const filteredByEmail = contacts.filter(
					(contact) => contact.email_address === email,
				);

				expect(filteredByEmail.length).to.equal(1); // Only one match found
				expect(filteredByEmail[0].email_address).to.equal(email); // The one match is our email
			});
		});
	});

	// TODO: This test is correct, but failing to a bug allowing contacts to be updated
	// regardless of the "Update Existing Subscriber?" option
	// Fix in issue 113 scheduled for 1.7.0.
	it('Do not update existing subscribers when they resubmit the signup form if option is unchecked', () => {
		// Not sure why we have to log in here, but we do
		cy.login(); // WP

		// Write test...
		cy.setSettingsOption('#mc_update_existing', false);

		// Navigate to the shortcode post
		cy.visit(blockPostPostURL);

		// Fill the form and submit it
		cy.get('#mc_mv_EMAIL').clear().type(email);
		cy.get('#mc_mv_FNAME').clear().type('Should not submit');
		cy.get('#mc_mv_LNAME').clear().type('Should not submit');

		// Verify error
		cy.submitFormAndVerifyError();
		cy.get('.mc_error_msg').contains(
			/This email address has already been subscribed to this list./i,
		);
	});
});
