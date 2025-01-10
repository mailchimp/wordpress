/* eslint-disable no-undef */
import { generateRandomEmail } from '../../support/functions/utility';

describe('JavaScript submission', () => {
	let blockPostPostURL;

	before(() => {
		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then((urls) => {
			blockPostPostURL = urls.blockPostPostURL;
		});

		cy.login(); // WP
		cy.mailchimpLoginIfNotAlreadyLoggedIn();

		cy.selectList('10up'); // Ensure list is selected, refreshes Mailchimp data with WP

		// Set JS support to enabled
		cy.setJavaScriptOption(true);

		// Disable double opt-in
		cy.setDoubleOptInOption(false);

		// Disable all merge fields
		cy.toggleMergeFields('uncheck');
	});

	beforeEach(() => {
		cy.visit(blockPostPostURL);
		setUpForm();
	});

	function setUpForm() {	
		// Step 1: Assert form contains setup elements
		// Email
		cy.get('#mc_signup').should('exist');
		cy.get('#mc_mv_EMAIL').should('exist');
		cy.get('#mc_signup_submit').should('exist');
	}

	function submitEmail(email) {
		// Step 2: Fill in the required fields (email and other merge fields)
		cy.get('#mc_mv_EMAIL').type(email);

		// Step 3: Assert that the submit button is enabled and exists
		cy.get('#mc_signup_submit').should('exist').and('be.enabled');

		// Step 4: Submit the form
		cy.get('#mc_signup_submit').click();
	}

	it('Disables the submit button before attempting submission', () => {
		submitEmail('invalidemail@--'); // Submit blank email

		// Step 4: Assert that the submit button is disabled after submitting the form
		cy.get('#mc_signup_submit').should('be.disabled');

		// Step 5: Verify that the form submission failed
		cy.get('.mc_error_msg').should('exist');
	});

	it('Perform post submit actions after successful submission', () => {
		const email = generateRandomEmail('javascript-submission-post-submit');
		submitEmail(email);

		// Step 5: Assert that the success message is displayed
		cy.get('.mc_success_msg').should('exist').contains('success', { matchCase: false });

		// Step 6: Verify that the form fields are cleared
		cy.get('#mc_mv_EMAIL').should('have.value', '');

		// Step 7: Verify that the submit button is re-enabled
		cy.get('#mc_signup_submit').should('be.enabled');

		cy.wait(1000);

		// Step 8: Assert that the form scrolled to the top
		cy.window().then((win) => {
			const scrollTop = win.pageYOffset || win.document.documentElement.scrollTop;
			expect(scrollTop).to.be.lessThan(500); // Doesn't scroll all the way to the top
		});

		// Step 9: Cleanup and delete contact
		cy.deleteContactFrom10UpList(email);
	});

	// TODO: This is a bug and is currently broken
	it.skip('Persist form data on Mailchimp API validation failure', () => {

		// Confirm that we received an error
		cy.get('#mc_signup_submit').click();
		cy.get('.mc_error_msg').should('exist');
		cy.get('.mc_error_msg').contains('Email Address:');
	});

	// TODO: BUG: Single opt-in is currently broken, but a fix is scheduled for 1.7.0
	it.skip('Success submission with JS support adds email to Mailchimp account as contact', () => {
		const email = generateRandomEmail('javascript-submission-verify-submission');
		submitEmail(email);

		// Step 5: Assert Mailchimp WP Success
		cy.get('.mc_success_msg').should('exist').contains('success', { matchCase: false });

		// Step 6: Verify that the contact was added to the Mailchimp account via the Mailchimp API
		cy.wait(5000)
			.verifyContactAddedToMailchimp(email, '10up');

		// Step 7: Cleanup and delete contact
		cy.deleteContactFrom10UpList(email);
	});
});