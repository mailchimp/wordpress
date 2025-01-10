/* eslint-disable no-undef */
describe('Update Existing Subscriber?', () => {
	let blockPostPostURL;

	// TODO: Do we want to ensure this is generated if it's not already in the test account?
	const email = 'existing-subscriber@10up.com'; // Static email, exists in test account already

	before(() => {
		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then((urls) => {
			blockPostPostURL = urls.blockPostPostURL;
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
		return Math.random().toString(36).substring(2, 2 + length);
	}

	it.skip('Update existing subscribers when they resubmit the signup form if option is checked', () => {
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
	
	it.skip('Verify that existing subscriber data is updated accurately without creating duplicate records', () => {
		// Write test...
	});

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
		cy.get('.mc_error_msg').contains(/This email address is already subscribed to the list./i);
	});
});
