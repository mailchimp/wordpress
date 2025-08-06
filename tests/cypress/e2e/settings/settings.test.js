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
		cy.get('input[value="Save Changes"]:visible').first().click();

		// Verify content options
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('.mc_custom_border_hdr').contains(header);
			cy.get('.mc_subheader').first().contains(subHeader);
			cy.get('.mc_signup_submit_button').contains(button);
		});
	});

	it('Admin can set Merge Fields Included settings', () => {
		// Ensure that all current merge tags are up to date and saved
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_list_id').select('10up');
		cy.get('input[value="Fetch list settings"]').click();

		// Uncheck all optional merge fields
		cy.get('#mc_mv_FNAME').uncheck();
		cy.get('#mc_mv_LNAME').uncheck();
		cy.get('#mc_mv_ADDRESS').uncheck();
		cy.get('#mc_mv_BIRTHDAY').uncheck();
		cy.get('#mc_mv_COMPANY').uncheck();
		cy.get('#mc_mv_COMPANY').trigger('change');
		cy.get('input[value="Save Changes"]:visible').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('input[id^="mc_mv_FNAME"]').should('not.exist');
			cy.get('input[id^="mc_mv_LNAME"]').should('not.exist');
			cy.get('input[name="mc_mv_ADDRESS[addr1]"]').should('not.exist'); // The address field has several inputs
			cy.get('input[id^="mc_mv_BIRTHDAY"]').should('not.exist');
			cy.get('input[id^="mc_mv_COMPANY"]').should('not.exist');
		});

		// Reset and recheck all merge fields
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_mv_FNAME').check();
		cy.get('#mc_mv_LNAME').check();
		cy.get('#mc_mv_ADDRESS').check();
		cy.get('#mc_mv_BIRTHDAY').check();
		cy.get('#mc_mv_COMPANY').check();
		cy.get('#mc_mv_COMPANY').trigger('change');
		cy.get('input[value="Save Changes"]:visible').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('input[id^="mc_mv_FNAME"]').should('exist');
			cy.get('input[id^="mc_mv_LNAME"]').should('exist');
			cy.get('input[name="mc_mv_ADDRESS[addr1]"]').should('exist'); // The address field has several inputs
			cy.get('input[id^="mc_mv_BIRTHDAY"]').should('exist');
			cy.get('input[id^="mc_mv_COMPANY"]').should('exist');
		});
	});

	it('Admin can update groups settings', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('input[id^="mc_show_interest_groups_"]').check();
		cy.get('input[id^="mc_show_interest_groups_"]').trigger('change');
		cy.get('input[value="Save Changes"]:visible').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('.mc_interests_header').should('exist');
			cy.get('.mc_interest').should('exist');
		});

		// Reset
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('input[id^="mc_show_interest_groups_"]').uncheck();
		cy.get('input[id^="mc_show_interest_groups_"]').trigger('change');
		cy.get('input[value="Save Changes"]:visible').first().click();

		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('.mc_interests_header').should('not.exist');
			cy.get('.mc_interest').should('not.exist');
		});
	});

	/**
	 * NOTE: "Use Double Opt-In (Recommended)?" and "Update existing subscribers?"
	 * are handled in `subscribe.test.js`
	 */
	it('Admin can set list options settings', () => {
		// Remove mailchimp JavaScript support.
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_use_unsub_link').check();
		cy.get('#mc_use_unsub_link').trigger('change');
		cy.get('input[value="Save Changes"]:visible').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('.mc_unsub_link').first().should('exist');
		});

		// Reset
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_use_unsub_link').uncheck();
		cy.get('#mc_use_unsub_link').trigger('change');
		cy.get('input[value="Save Changes"]:visible').first().click();

		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('.mc_unsub_link').should('not.exist');
		});
	});

	it('Proper error message should display if unsubscribed user try to subscribe', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_double_optin').uncheck();
		cy.get('#mc_update_existing').check();
		cy.get('#mc_update_existing').trigger('change');
		cy.get('input[value="Save Changes"]:visible').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('input[id^="mc_mv_EMAIL"]').should('exist');
			cy.get('input[id^="mc_mv_EMAIL"]').clear().type('unsubscribed_user@gmail.com');
			cy.get('.mc_signup_submit_button').should('exist');
			cy.get('.mc_signup_submit_button').click();
			cy.get('.mc_error_msg').should('exist');
			cy.get('.mc_error_msg').contains(
				'The email address cannot be subscribed because it was previously unsubscribed, bounced, or is under review. Please sign up here.',
			);
		});

		// Reset
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_double_optin').check();
		cy.get('#mc_update_existing').check();
		cy.get('#mc_update_existing').trigger('change');
		cy.get('input[value="Save Changes"]:visible').first().click();
	});

	it('Form data should persist if validation fails', () => {
		// Remove mailchimp CSS.
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_use_javascript').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			const firstName = 'John';
			const lastName = 'Doe';
			cy.visit(url);
			cy.get('input[id^="mc_mv_EMAIL"]').should('exist');
			cy.get('input[id^="mc_mv_FNAME"]').clear().type(firstName);
			cy.get('input[id^="mc_mv_LNAME"]').clear().type(lastName);
			cy.get('.mc_signup_submit_button').should('exist');
			cy.get('.mc_signup_submit_button').click();
			cy.get('.mc_error_msg').should('exist');
			cy.get('.mc_error_msg').contains('Please enter your email address.');
			cy.get('input[id^="mc_mv_FNAME"]').should('have.value', firstName);
			cy.get('input[id^="mc_mv_LNAME"]').should('have.value', lastName);
		});
	});

	it('Admin can logout', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mailchimp_sf_oauth_connect').should('not.exist');
		cy.get('input[value="Logout"]').click();

		// connect to "Mailchimp" Account button should be visible.
		cy.get('#mailchimp_sf_oauth_connect').should('exist');
	});

	// TODO: Add case for separate account login and settings get reset.
	it('Ensure settings persist between logging out and logging back in of Mailchimp account', () => {
		// Step 1: Visit Mailchimp settings page
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mailchimp_sf_oauth_connect').should('not.exist');

		// Step 2: Set an option different from the default
		const customHeader = 'My Custom Header';
		cy.get('#mc_header_content').clear().type(customHeader);
		cy.get('input[value="Save Changes"]:visible').first().click();

		// Verify the custom header is saved
		cy.get('.notice.notice-success.is-dismissible')
			.last()
			.contains('Successfully Updated your List Subscribe Form Settings!');
		cy.get('#mc_header_content').should('have.value', customHeader);

		// Step 3: Log out of the Mailchimp account
		cy.get('input[value="Log out"]').click();

		// Verify the logout was successful
		cy.get('#mailchimp_sf_oauth_connect').should('exist');

		// Step 4: Log in back
		cy.mailchimpLogin();
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Step 5: Ensure the original settings persist
		cy.get('#mc_header_content').should('have.value', customHeader);
	});

	it('Spam protection should work as expected', () => {
		// Show error message to spam bots.
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('.mc_signup_form').should('exist');
			cy.get('input[name="mailchimp_sf_alt_email"]').then((el) => {
				el.val('123');
			});
			cy.get('.mc_signup_submit_button').should('exist');
			cy.get('.mc_signup_submit_button').click();
			cy.get('.mc_error_msg').should('exist');
			cy.get('.mc_error_msg').contains(
				"We couldn't process your submission as it was flagged as potential spam",
			);

			// Normal user should not see the error message.
			cy.visit(url);
			cy.get('.mc_signup_form').should('exist');
			cy.get('.mc_signup_submit_button').should('exist');
			cy.get('.mc_signup_submit_button').click();
			cy.get('.mc_error_msg').should('exist');
			cy.get('.mc_error_msg').contains('Please enter your email address.');
		});
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
					cy.get('.user-profile-name').should('be.visible');
					cy.get('input[value="Log out"]').should('exist');

					cy.selectList('10up');

					// Verify default settings
					cy.get('#mc_header_content').should('have.value', 'Sign up for 10up');
					cy.get('#mc_subheader_content').should('have.value', '');
					cy.get('#mc_submit_text').should('have.value', 'Subscribe');
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

	it('Admin can view form preview on the settings page', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('h2.mailchimp-sf-settings-table-title')
			.contains('Form preview')
			.should('be.visible');
	});

	it('Form preview should reflect changes made on the settings page', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_header_content').clear().type('My Custom Header');
		cy.wait(1000);
		cy.get('.mailchimp-sf-form-preview .mc_custom_border_hdr').should(
			'have.text',
			'My Custom Header',
		);

		cy.get('#mc_subheader_content').clear().type('My Custom Subheader');
		cy.wait(1000);
		cy.get('.mailchimp-sf-form-preview #mc_subheader').contains('My Custom Subheader');

		cy.get('#mc_submit_text').clear().type('My Custom Button');
		cy.wait(1000);
		cy.get('.mailchimp-sf-form-preview #mc_signup_submit').contains('My Custom Button');

		// Field options
		cy.get('#mc_mv_FNAME').uncheck();
		cy.wait(1000);
		cy.get('.mailchimp-sf-form-preview input#mc_mv_FNAME').should('not.exist');

		cy.get('#mc_mv_LNAME').uncheck();
		cy.wait(1000);
		cy.get('.mailchimp-sf-form-preview input#mc_mv_LNAME').should('not.exist');

		cy.get('#mc_mv_FNAME').check();
		cy.wait(1000);
		cy.get('.mailchimp-sf-form-preview input#mc_mv_FNAME').should('exist');

		cy.get('#mc_mv_LNAME').check();
		cy.wait(1000);
		cy.get('.mailchimp-sf-form-preview input#mc_mv_LNAME').should('exist');

		// Unsubscribe link
		cy.get('#mc_use_unsub_link').check();
		cy.wait(1000);
		cy.get('.mailchimp-sf-form-preview #mc_unsub_link').should('exist');

		cy.get('#mc_use_unsub_link').uncheck();
		cy.wait(1000);
		cy.get('.mailchimp-sf-form-preview #mc_unsub_link').should('not.exist');

		// Groups
		cy.get('input[id^="mc_show_interest_groups_"]').check();
		cy.wait(1000);
		cy.get('.mailchimp-sf-form-preview .mc_interests_header').should('exist');
		cy.get('.mailchimp-sf-form-preview .mc_interest').should('exist');

		cy.get('input[id^="mc_show_interest_groups_"]').uncheck();
		cy.wait(1000);
		cy.get('.mailchimp-sf-form-preview .mc_interests_header').should('not.exist');
		cy.get('.mailchimp-sf-form-preview .mc_interest').should('not.exist');
	});
});
