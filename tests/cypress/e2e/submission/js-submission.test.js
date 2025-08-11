/* eslint-disable no-undef */
import { generateRandomEmail } from '../../support/functions/utility';

describe('JavaScript submission', () => {
	let blockPostPostURL;

	before(() => {
		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then((urls) => {
			({ blockPostPostURL } = urls);
		});

		cy.login(); // WP
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		cy.selectList('10up'); // Ensure list is selected, refreshes Mailchimp data with WP

		// Disable double opt-in
		cy.setDoubleOptInOption(false);

		// Disable all merge fields
		cy.toggleMergeFields('uncheck');
	});

	function setUpForm() {
		// Step 1: Assert form contains setup elements
		// Email
		cy.get('.mc_signup_form').should('exist');
		cy.get('input[id^="mc_mv_EMAIL"]').should('exist');
		cy.get('.mc_signup_submit_button').should('exist');
	}

	function submitEmail(email) {
		// Step 2: Fill in the required fields (email and other merge fields)
		cy.get('input[id^="mc_mv_EMAIL"]').type(email);

		// Step 3: Assert that the submit button is enabled and exists
		cy.get('.mc_signup_submit_button').should('exist').and('be.enabled');

		// Step 4: Submit the form
		cy.get('.mc_signup_submit_button').click();
	}

	beforeEach(() => {
		cy.visit(blockPostPostURL);
		setUpForm();
	});

	it('Disables the submit button before attempting submission', () => {
		submitEmail('invalidemail@test.com'); // Submit blank email

		// Step 4: Assert that the submit button is disabled after submitting the form
		cy.get('.mc_signup_submit_button').should('be.disabled');

		// Step 5: Verify that the form submission failed
		cy.get('.mc_error_msg').should('exist');
	});

	it('Perform post submit actions after successful submission', () => {
		const email = generateRandomEmail('javascript-submission-post-submit');
		submitEmail(email);

		// Step 5: Assert that the success message is displayed
		cy.get('.mc_success_msg').should('exist').contains('success', { matchCase: false });

		// Step 6: Verify that the form fields are cleared
		cy.get('input[id^="mc_mv_EMAIL"]').should('have.value', '');

		// Step 7: Verify that the submit button is re-enabled
		cy.get('.mc_signup_submit_button').should('be.enabled');

		cy.wait(1000);

		// Step 8: Assert that the form scrolled to the top
		cy.window().then((win) => {
			const scrollTop = win.pageYOffset || win.document.documentElement.scrollTop;
			expect(scrollTop).to.be.lessThan(500); // Doesn't scroll all the way to the top
		});

		// Step 9: Cleanup and delete contact
		cy.deleteContactFromList(email);
	});

	it('Success submission with JS support adds email to Mailchimp account as contact', () => {
		const email = generateRandomEmail('javascript-submission-verify-submission');
		submitEmail(email);

		// Step 5: Assert Mailchimp WP Success
		cy.get('.mc_success_msg').should('exist').contains('success', { matchCase: false });

		// Step 6: Verify that the contact was added to the Mailchimp account via the Mailchimp API
		cy.wait(5000).verifyContactInMailchimp(email);

		// Step 7: Cleanup and delete contact
		cy.deleteContactFromList(email);
	});
});
