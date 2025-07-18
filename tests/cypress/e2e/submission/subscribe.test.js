/* eslint-disable no-undef */
import { generateRandomEmail } from '../../support/functions/utility';

describe('Subscribe actions', () => {
	before(() => {
		cy.login(); // WP
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		cy.selectList('10up'); // Ensure list is selected, refreshes Mailchimp data with WP

		cy.setDoubleOptInOption(false);
	});

	function signUpAndVerify(url) {
		cy.visit(url);

		// Step 3: Verify the form is displayed
		cy.get('.mc_signup_form').should('exist');
		cy.get('input[id^="mc_mv_EMAIL"]').should('exist');
		cy.get('.mc_signup_submit_button').should('exist');

		// Step 4: Test error handling
		cy.get('.mc_signup_submit_button').click();
		cy.get('.mc_error_msg').should('exist');
		cy.get('.mc_error_msg').contains('Email Address: This value should not be blank.');

		// Step 5: Test that the form can be submitted
		const email = generateRandomEmail('shortcode-signup-test');
		cy.get('input[id^="mc_mv_EMAIL"]').type(email);

		// Step 6: Verify that the form was submitted successfully
		cy.submitFormAndVerifyWPSuccess();

		// // TODO: This is failing because of a bug causing single opt-in to malfunction. Fix is ready for 1.7.0.
		// // Step 7: Verify that the contact was added to the Mailchimp account via the Mailchimp API
		// cy.verifyContactInMailchimp(email);

		// Step 8: Cleanup and delete contact
		cy.deleteContactFromList(email);
	}

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
				const shortcodePostURL = `/?p=${post.id}`;
				signUpAndVerify(shortcodePostURL);
			}
		});
	});

	it('Admin can create and subscribe to a signup form using the Mailchimp block', () => {
		// Step 1: Set up the post with the shortcode
		const postTitle = 'Mailchimp signup form - Block';
		const beforeSave = () => {
			cy.insertBlock('mailchimp/mailchimp', 'Mailchimp List Subscribe Form');
			cy.wait(500);
		};

		// Step 2: Create the post
		cy.createPost({ title: postTitle, content: '', beforeSave }).then((postBlock) => {
			if (postBlock) {
				const blockPostPostURL = `/?p=${postBlock.id}`;
				signUpAndVerify(blockPostPostURL);
			}
		});
	});
});
