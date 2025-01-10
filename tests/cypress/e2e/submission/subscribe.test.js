/* eslint-disable no-undef */
describe('Subscribe actions', () => {
	let shortcodePostURL;
	let blockPostPostURL;

	before(() => {
		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then((urls) => {
			shortcodePostURL = urls.shortcodePostURL;
			blockPostPostURL = urls.blockPostPostURL;
		});

		cy.login(); // WP
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		cy.selectList('10up'); // Ensure list is selected, refreshes Mailchimp data with WP
	});

	/**
	 * - Test form creation
	 * - Test form display (basic)
	 * - Test form error handling (basic)
	 * - Test form submission
	 * - Test that the contact was added to the Mailchimp account via the Mailchimp API
	 */
	it('Admin can create and subscribe to a signup form using the shortcode', () => {
		// Step 1: Set up the post with the shortcode
		const postTitle = 'Mailchimp signup form - shortcode';
		const beforeSave = () => {
			cy.insertBlock('core/shortcode').then((id) => {
				cy.getBlockEditor()
					.find(`#${id} .blocks-shortcode__textarea`)
					.clear()
					.type('[mailchimpsf_form]');
			});
		};

		// Step 2: Create the post
		cy.createPost({ title: postTitle, content: '', beforeSave }).then((post) => {
			if (post) {
				shortcodePostURL = `/?p=${post.id}`;
				cy.visit(shortcodePostURL);

				// Step 3: Verify the form is displayed
				cy.get('#mc_signup').should('exist');
				cy.get('#mc_mv_EMAIL').should('exist');
				cy.get('#mc_signup_submit').should('exist');

				// Step 4: Test error handling
				cy.get('#mc_signup_submit').click();
				cy.get('.mc_error_msg').should('exist');
				cy.get('.mc_error_msg').contains('Email Address: This value should not be blank.');

				// Step 5: Test that the form can be submitted
				// TODO: Is this email address name a security hazard? "@example.com" emails will not pass validation.
				const email = 'max.garceau+shortcodesignuptest@10up.com';
				cy.get('#mc_mv_EMAIL').type(email);

				// // TODO: This is failing because we need to confirm the test email address subscription
				// // TODO: We will also have to delete the contact before each form submission via the Mailchimp API
				// Step 6: Verify that the form was submitted successfully
				// cy.submitFormAndVerifyWPSuccess();

				// // Step 7: Verify that the contact was added to the Mailchimp account via the Mailchimp API
				// cy.verifyContactAddedToMailchimp(email, '10up');
			}
		});
	});

	it('Admin can create and subscribe to a signup form using the Mailchimp block', () => {
		const postTitle = 'Mailchimp signup form - Block';
		const beforeSave = () => {
			cy.insertBlock('mailchimp/mailchimp', 'Mailchimp List Subscribe Form');
			cy.wait(500);
		};
		cy.createPost({ title: postTitle, content: '', beforeSave }).then((postBlock) => {
			if (postBlock) {
				blockPostPostURL = `/?p=${postBlock.id}`;
				cy.visit(blockPostPostURL);
				cy.get('#mc_signup').should('exist');
				cy.get('#mc_mv_EMAIL').should('exist');
				cy.get('#mc_signup_submit').should('exist');

				// Test error handling
				cy.get('#mc_signup_submit').click();
				cy.get('.mc_error_msg').should('exist');
				cy.get('.mc_error_msg').contains('Email Address: This value should not be blank.');
			}
		});
	});

	// TODO: BLOCKED - Need access to a service that can catch test emails so we can finish the email verification process.
	it.skip('When double opt-in is enabled a subscriber must verify their email before their submission displays in Mailchimp', () => {
		
	});

	// TODO: This is a known bug. The back link goes to a 404 page on the Mailchimp account.
	it.skip('after a double optin subscribe a user can click a back link to return to the website', () => {
		
	});

	it.skip('Update existing subscribers when they resubmit the signup form if option is checked', () => {

	});

	it.skip('Do not update existing subscribers when they resubmit the signup form if option is unchecked', () => {

	});

	// This answers the question whether a user can resubscribe after unsubscribing or not.
	it.skip('Subscribers who have previously unsubscribed should be able to resubscribe using the signup form', () => {

	});
});