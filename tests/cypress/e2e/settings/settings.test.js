/* eslint-disable no-undef */
describe('Admin can update plugin settings', () => {
	let shortcodePostURL;
	let blockPostPostURL;

	before(() => {
		// Load the post URLs from the JSON file
		cy.fixture('postUrls').then((urls) => {
			({ shortcodePostURL, blockPostPostURL } = urls);
		});

		cy.login(); // WP
		cy.mailchimpLoginIfNotAlreadyLoggedIn();
		cy.selectList('10up'); // Ensure a list is selected
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
				.should('be.visible') // Check if the button is visible
				.and('not.be.disabled'); // Ensure the button is not disabled

			// Ensure that custom CSS does not cover submit button
			cy.get('#mc_signup_submit').then(($el) => {
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

	/**
	 * NOTE: "Use Double Opt-In (Recommended)?" and "Update existing subscribers?"
	 * are handled in `subscribe.test.js`
	 */
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

	// TODO: Add case for separate account login and settings get reset.
	it('Ensure settings persist between logging out and logging back in of Mailchimp account', () => {
		// Step 1: Visit Mailchimp settings page
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mailchimp_sf_oauth_connect').should('not.exist');

		// Step 2: Set an option different from the default
		const customHeader = 'My Custom Header';
		cy.get('#mc_header_content').clear().type(customHeader);
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify the custom header is saved
		cy.get('#mc-message .success_msg').contains(
			'Successfully Updated your List Subscribe Form Settings!',
		);
		cy.get('#mc_header_content').should('have.value', customHeader);

		// Step 3: Log out of the Mailchimp account
		cy.get('input[value="Logout"]').click();

		// Verify the logout was successful
		cy.get('#mailchimp_sf_oauth_connect').should('exist');

		// Step 4: Log in back
		cy.mailchimpLogin();
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Step 5: Ensure the original settings persist
		cy.get('#mc_header_content').should('have.value', customHeader);
	});

	it('The default settings populate as expected', () => {
		const options = [
			'mc_header_content',
			'mc_subheader_content',
			'mc_submit_text',
			'mc_nuke_all_styles',
			'mc_custom_style',
			'mc_form_border_width',
			'mc_form_border_color',
			'mc_form_background',
			'mc_form_text_color',
			'mc_update_existing',
			'mc_double_optin',
			'mc_user_id',
			'mc_use_javascript',
			'mc_use_datepicker',
			'mc_use_unsub_link',
			'mc_list_id',
			'mc_list_name',
			'mc_interest_groups',
			'mc_merge_vars',
		];

		// Clear all options
		cy.getListId('10up').then((listId) => {
			cy.getMergeFields(listId).then((mergeFields) => {
				const mergeFieldOptions = mergeFields.map((field) => `mc_mv_${field.tag}`);
				options.push(...mergeFieldOptions);
				cy.wpCli(`wp option delete ${options.join(' ')}`).then(() => {
					cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
					cy.mailchimpLogout();
					cy.mailchimpLogin();
					cy.get('.mc-user h3').contains('Logged in as: ');
					cy.get('input[value="Logout"]').should('exist');

					cy.selectList('10up');

					// Verify default settings
					cy.get('#mc_header_content').should('have.value', 'Sign up for 10up');
					cy.get('#mc_subheader_content').should('have.value', '');
					cy.get('#mc_submit_text').should('have.value', 'Subscribe');
					cy.get('#mc_nuke_all_styles').should('not.be.checked');
					cy.get('#mc_custom_style').should('not.be.checked');
					cy.get('#mc_form_border_width').should('have.value', '1');
					cy.get('#mc_form_border_color').should('have.value', 'E0E0E0');
					cy.get('#mc_form_background').should('have.value', 'FFFFFF');
					cy.get('#mc_form_text_color').should('have.value', '3F3F3f');
					cy.get('#mc_update_existing').should('not.be.checked');
					cy.get('#mc_double_optin').should('be.checked');
					cy.get('#mc_use_unsub_link').should('not.be.checked');
					cy.get('#mc_mv_FNAME').should('be.checked');
					cy.get('#mc_mv_LNAME').should('be.checked');
					cy.get('#mc_mv_ADDRESS').should('be.checked');
					cy.get('#mc_mv_BIRTHDAY').should('be.checked');
					cy.get('#mc_mv_COMPANY').should('be.checked');
					cy.get('#mc_mv_PHONE').should('be.checked');
				});
			});
		});
	});
});
