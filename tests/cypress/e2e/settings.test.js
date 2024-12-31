/* eslint-disable no-undef */
describe('Admin can update plugin settings', () => {
	let shortcodePostURL;
	let blockPostPostURL;

	before(() => {
		// TODO: Initialize tests from a blank state
		// TODO: Wipe WP data related to a users options
		// TODO: Delete all contacts in a users Mailchimp account
		// TODO: Ensure the default audience list is "10up"
		// TODO: Include all merge fields as "Visible" in the users Mailchimp account

		// Load the post URLs from the JSON file
		cy.fixture('postUrls.json').then((urls) => {
			shortcodePostURL = urls.shortcodePostURL;
			blockPostPostURL = urls.blockPostPostURL;
		});

		cy.login(); // WP
        cy.mailchimpLoginIfNotAlreadyLoggedIn();

		// Call mailchimpLists once and store the result in the alias 'mailchimpLists'
		cy.getMailchimpLists().then((mailchimpLists) => {
			Cypress.env('mailchimpLists', mailchimpLists); // Save globally
		});
	});

	it('All lists from user\'s account populate the WP admin dropdown list', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		const $wpLists = cy.get('#mc_list_id'); // Lists from the WP admin dropdown
		const mailchimpLists = Cypress.env('mailchimpLists');

		// Verify that the same number of lists exist in the dropdown as in the Mailchimp account
		$wpLists.should('have.length', mailchimpLists.length);

		mailchimpLists.forEach((list) => {
			// Verify that all Mailchimp account lists exist in dropdown
			cy.get('#mc_list_id').should('contain', list.name);
		});
	});

	it('Admin can see list and save it', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Verify that list can be saved
		cy.get('.mc-h2').contains('Your Lists');
		cy.get('#mc_list_id').select('10up');
		cy.get('input[value="Update List"]').click();
		cy.get('#mc-message .success_msg b').contains('Success!');
	});

	// TODO: BLOCKED - Need separate Mailchimp user to finish this test
	it.skip('Admin that has never saved a list can not see the form on the front end', () => {
		// Step 1: Log the user out of the current account (if logged in)
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.mailchimpLogout();
	
		// Verify the user is logged out
		cy.get('#mailchimp_sf_oauth_connect').should('exist');
	
		// Step 2: Log in with a test user account that has never saved a list
		// TODO: BLOCKED - We need a second user here to finish this test
		cy.mailchimpLogin('test_user_no_list@mailchimp.com', 'password123'); // TODO: CHANGE LOG IN HERE
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
	
		// Verify no list is saved for the test user
		cy.get('#mc_list_id').should('have.value', ''); // Assuming empty value indicates no list is saved
	
		// Step 3: Verify the signup form is not displayed on the frontend
		cy.visit('/'); // Navigate to the frontend homepage
		cy.get('#mc_signup').should('not.exist'); // Ensure the form does not exist
	});

	// TODO: Default settings are populated as expected
	it.skip('The default settings populate as expected', () => {
		// Test here...
	});

	/**
	 * - Test form creation
	 * - Test form display (basic)
	 * - Test form error handling (basic)
	 * - Test form submission
	 * - Test that the contact was added to the Mailchimp account via the Mailchimp API
	 */
	it('Admin can create and submit a Signup form using the shortcode', () => {
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
				cy.get('#mc_signup_submit').click();

				// Step 6: Verify that the form was submitted successfully
				cy.get('.mc_success_msg').should('exist');

				// // TODO: This is failing because we need to confirm the test email address subscription
				// // Step 7: Verify that the contact was added to the Mailchimp account via the Mailchimp API
				// const mailchimpLists = Cypress.env('mailchimpLists');
				// const listId = mailchimpLists.find((list) => list.name === '10up').id;
				// // Get the contacts from the list
				// cy.getContactsFromAList(listId).then((contacts) => {
				// 	console.log('Contacts:', contacts);
				// 	// Verify that the contact was added to the list
				// 	const contactJustRegistered = contacts.find((c) => c.email_address === email);
				// 	expect(contactJustRegistered).to.exist;
				// });
			}
		});
	});

	it('Admin can create a Signup form using Mailchimp block', () => {
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

	it('Admin can set content options for signup form', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Set content options
		const header = 'Subscribe to our newsletter';
		const subHeader =
			'Join our mailing list to receive the latest news and updates from our team.';
		const button = 'Subscribe Now';
		cy.get('#mc_header_content').clear().type(header);
		cy.get('#mc_subheader_content').clear().type(subHeader);
		cy.get('#mc_submit_text').clear().type(button);
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify content options
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('.mc_custom_border_hdr').contains(header);
			cy.get('#mc_subheader').contains(subHeader);
			cy.get('#mc_signup_submit').contains(button);
		});
	});

	it('Admin can remove mailchimp CSS', () => {
		// Remove mailchimp CSS.
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_nuke_all_styles').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_subheader').should('not.have.css', 'margin-bottom', '18px');
		});

		// Enable mailchimp CSS.
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_nuke_all_styles').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_subheader').should('have.css', 'margin-bottom', '18px');
		});
	});

	it('Admin can set custom styling on signup form', () => {		
		// Enable custom styling and set values.
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_custom_style').check();
		cy.get('#mc_form_border_width').clear().type('10');
		cy.get('#mc_form_border_color').clear().type('000000');
		cy.get('#mc_form_text_color').clear().type('FF0000');
		cy.get('#mc_form_background').clear().type('00FF00');
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_signup form').should('have.css', 'border-width', '10px');
			cy.get('#mc_signup form').should('have.css', 'border-color', 'rgb(0, 0, 0)');
			cy.get('#mc_signup form').should('have.css', 'color', 'rgb(255, 0, 0)');
			cy.get('#mc_signup form').should('have.css', 'background-color', 'rgb(0, 255, 0)');

			// Form is able to be submitted with custom styles
			cy.get('#mc_signup_submit')
				.scrollIntoView({ offset: { top: -100, left: 0 } })
				.should('be.visible')   // Check if the button is visible
				.and('not.be.disabled'); // Ensure the button is not disabled
				// .click();               // Perform the click action

			// Ensure that custom CSS does not cover submit button
			cy.get('#mc_signup_submit')
			.then(($el) => {
				const rect = $el[0].getBoundingClientRect();
				
				// Check that the element is within the viewport
				cy.window().then((win) => {
					const windowHeight = win.innerHeight;
					const windowWidth = win.innerWidth;

					expect(rect.top).to.be.greaterThan(0);
					expect(rect.left).to.be.greaterThan(0);
					expect(windowHeight).to.be.greaterThan(0);
					expect(windowWidth).to.be.greaterThan(0);
					expect(rect.bottom).to.be.lessThan(windowHeight);
					expect(rect.right).to.be.lessThan(windowWidth);
					});

					// Check if the center of the element is not covered by another element
					const centerX = rect.x + $el[0].offsetWidth / 2;
					const centerY = rect.y + $el[0].offsetHeight / 2;
				
					cy.document().then((doc) => {
						const topElement = doc.elementFromPoint(centerX, centerY);
						expect(topElement).to.equal($el[0]);
					});
				});
		});

		// Reset
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_custom_style').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify base styles
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_signup form').should('not.have.css', 'border-width', '10px');
			cy.get('#mc_signup form').should('not.have.css', 'border-color', 'rgb(0, 0, 0)');
			cy.get('#mc_signup form').should('not.have.css', 'color', 'rgb(255, 0, 0)');
			cy.get('#mc_signup form').should('not.have.css', 'background-color', 'rgb(0, 255, 0)');
		});
	});

	it('Admin can set Merge Fields Included settings', () => {
		// Ensure that all current merge tags are up to date and saved
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_list_id').select('10up');
		cy.get('input[value="Update List"]').click();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Uncheck all optional merge fields
		cy.get('#mc_mv_FNAME').uncheck();
		cy.get('#mc_mv_LNAME').uncheck();
		cy.get('#mc_mv_ADDRESS').uncheck();
		cy.get('#mc_mv_BIRTHDAY').uncheck();
		cy.get('#mc_mv_COMPANY').uncheck();
		cy.get('#mc_mv_PHONE').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_mv_FNAME').should('not.exist');
			cy.get('#mc_mv_LNAME').should('not.exist');
			cy.get('#mc_mv_ADDRESS-addr1').should('not.exist'); // The address field has several inputs
			cy.get('#mc_mv_BIRTHDAY').should('not.exist');
			cy.get('#mc_mv_COMPANY').should('not.exist');
			cy.get('#mc_mv_PHONE').should('not.exist');
		});

		// Reset and recheck all merge fields
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_mv_FNAME').check();
		cy.get('#mc_mv_LNAME').check();
		cy.get('#mc_mv_ADDRESS').check();
		cy.get('#mc_mv_BIRTHDAY').check();
		cy.get('#mc_mv_COMPANY').check();
		cy.get('#mc_mv_PHONE').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_mv_FNAME').should('exist');
			cy.get('#mc_mv_LNAME').should('exist');
			cy.get('#mc_mv_ADDRESS-addr1').should('exist'); // The address field has several inputs
			cy.get('#mc_mv_BIRTHDAY').should('exist');
			cy.get('#mc_mv_COMPANY').should('exist');
			cy.get('#mc_mv_PHONE').should('exist');
		});
	});

	it('Admin can set list options settings', () => {
		// Remove mailchimp JavaScript support.
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_use_javascript').uncheck();
		cy.get('#mc_use_datepicker').uncheck();
		cy.get('#mc_use_unsub_link').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_submit_type').should('have.value', 'html');
			cy.get('#mc_mv_BIRTHDAY').should('not.have.class', 'hasDatepicker');
			cy.get('#mc_mv_BIRTHDAY').click();
			cy.get('#ui-datepicker-div').should('not.exist');
			cy.get('#mc_unsub_link').should('exist');
		});

		// Reset
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_use_javascript').check();
		cy.get('#mc_use_datepicker').check();
		cy.get('#mc_use_unsub_link').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_submit_type').should('have.value', 'js');
			cy.get('#mc_mv_BIRTHDAY').should('have.class', 'hasDatepicker');
			cy.get('#mc_mv_BIRTHDAY').click();
			cy.get('#ui-datepicker-div').should('exist');
			cy.get('#mc_unsub_link').should('not.exist');
		});
	});

	// TODO: BLOCKED - Need separate Mailchimp user to finish this test
	it.skip('Ensure settings persist between logging out and logging back in of Mailchimp account', () => {
		// Step 1: Visit Mailchimp settings page
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mailchimp_sf_oauth_connect').should('not.exist');
	
		// Step 2: Set an option different from the default
		const customHeader = 'My Custom Header';
		cy.get('#mc_header_content').clear().type(customHeader);
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	
		// Verify the custom header is saved
		cy.get('#mc-message .success_msg b').contains('Success!');
		cy.get('#mc_header_content').should('have.value', customHeader);
	
		// Step 3: Log out of the Mailchimp account
		cy.get('input[value="Logout"]').click();
	
		// Verify the logout was successful
		cy.get('#mailchimp_sf_oauth_connect').should('exist');
	
		// Step 4: Log in with a different Mailchimp account
		// TODO: BLOCKED - We need a separate Mailchimp account to test the login here
		cy.mailchimpLogin('different@mailchimp.com', 'password123'); // TODO: CHANGE LOG IN HERE
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
	
		// Verify the default options are displayed for the new account
		cy.get('#mc_header_content').should('not.have.value', customHeader); // Expect default value
	
		// Step 5: Set another option with the second account to test persistence
		const differentHeader = 'Another Custom Header';
		cy.get('#mc_header_content').clear().type(differentHeader);
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	
		// Verify the new setting is saved for the second account
		cy.get('#mc-message .success_msg b').contains('Success!');
		cy.get('#mc_header_content').should('have.value', differentHeader);
	
		// Step 6: Log back in with the original Mailchimp account
		cy.get('input[value="Logout"]').click();
		cy.mailchimpLogin(); // Default to user set in env
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
	
		// Step 7: Ensure the original settings persist
		cy.get('#mc_header_content').should('have.value', customHeader);
	});
});
