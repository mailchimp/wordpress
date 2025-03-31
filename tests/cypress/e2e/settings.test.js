/* eslint-disable no-undef */
describe('Admin can update plugin settings', () => {
	let shortcodePostURL = '/mailchimp-signup-form-shortcode';
	let blockPostPostURL = '/mailchimp-signup-form-block';

	before(() => {
		cy.login();
	});

	it('Admin can see list and save it', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		cy.get('.mc-h2').contains('Your Lists');
		cy.get('#mc_list_id').select('10up');
		cy.get('input[value="Update List"]').click();
		cy.get('#mailchimp-sf-settings-page .notice.notice-success p').contains('Success!');
	});

	it('Admin can create a Signup form using the shortcode', () => {
		const postTitle = 'Mailchimp signup form - shortcode';
		const beforeSave = () => {
			cy.insertBlock('core/shortcode').then((id) => {
				cy.getBlockEditor()
					.find(`#${id} .blocks-shortcode__textarea`)
					.clear()
					.type('[mailchimpsf_form]');
			});
		};
		cy.createPost({ title: postTitle, content: '', beforeSave }).then((post) => {
			if (post) {
				shortcodePostURL = `/?p=${post.id}`;
				cy.visit(shortcodePostURL);
				cy.get('#mc_signup').should('exist');
				cy.get('#mc_mv_EMAIL').should('exist');
				cy.get('#mc_signup_submit').should('exist');
				cy.get('#mc_signup_submit').click();
				cy.get('.mc_error_msg').should('exist');
				cy.get('.mc_error_msg').contains('Email Address: This value should not be blank.');
			}
		});
	});

	it('Admin can create a Signup form using Mailchimp block', () => {
		const postTitle = 'Mailchimp signup form - Block';
		// Creating a post with Mailchimp block using wpCLI to test the backward compatibility of the existing block.
		cy.wpCli(
			`wp post create --post_title='${postTitle}' --post_content='<!-- wp:mailchimp/mailchimp -->' --post_status='publish' --porcelain`,
		).then((response) => {
			blockPostPostURL = `/?p=${response.stdout}`;
			cy.visit(blockPostPostURL);
			cy.get('#mc_signup').should('exist');
			cy.get('#mc_mv_EMAIL').should('exist');
			cy.get('#mc_signup_submit').should('exist');
			cy.get('#mc_signup_submit').click();
			cy.get('.mc_error_msg').should('exist');
			cy.get('.mc_error_msg').contains('Email Address: This value should not be blank.');
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

	it('Admin can set Merge Fields Included settings', () => {
		// Remove mailchimp CSS.
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_mv_FNAME').uncheck();
		cy.get('#mc_mv_LNAME').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_mv_FNAME').should('not.exist');
			cy.get('#mc_mv_LNAME').should('not.exist');
		});

		// Reset
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_mv_FNAME').check();
		cy.get('#mc_mv_LNAME').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_mv_FNAME').should('exist');
			cy.get('#mc_mv_LNAME').should('exist');
		});
	});

	it('Admin can update groups settings', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('input[id^="mc_show_interest_groups_"]').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('.mc_interests_header').should('exist');
			cy.get('.mc_interest').should('exist');
		});

		// Reset
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('input[id^="mc_show_interest_groups_"]').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('.mc_interests_header').should('not.exist');
			cy.get('.mc_interest').should('not.exist');
		});
	});

	it('Admin can set list options settings', () => {
		// display unsubscribe link.
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_use_unsub_link').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_unsub_link').should('exist');
		});

		// Reset
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_use_unsub_link').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_unsub_link').should('not.exist');
		});
	});

	it('Proper error message should display if unsubscribed user try to subscribe', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_double_optin').uncheck();
		cy.get('#mc_update_existing').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		[shortcodePostURL, blockPostPostURL].forEach((url) => {
			cy.visit(url);
			cy.get('#mc_mv_EMAIL').should('exist');
			cy.get('#mc_mv_EMAIL').clear().type('unsubscribed_user@gmail.com');
			cy.get('#mc_signup_submit').should('exist');
			cy.get('#mc_signup_submit').click();
			cy.get('.mc_error_msg').should('exist');
			cy.get('.mc_error_msg').contains(
				'The email address cannot be subscribed because it was previously unsubscribed, bounced, or is under review. Please sign up here.',
			);
		});

		// Reset
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_double_optin').check();
		cy.get('#mc_update_existing').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
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
			cy.get('#mc_mv_EMAIL').should('exist');
			cy.get('#mc_mv_FNAME').clear().type(firstName);
			cy.get('#mc_mv_LNAME').clear().type(lastName);
			cy.get('#mc_signup_submit').should('exist');
			cy.get('#mc_signup_submit').click();
			cy.get('.mc_error_msg').should('exist');
			cy.get('.mc_error_msg').contains('Email Address: This value should not be blank.');
			cy.get('#mc_mv_FNAME').should('have.value', firstName);
			cy.get('#mc_mv_LNAME').should('have.value', lastName);
		});
	});

	it('Admin can logout', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mailchimp_sf_oauth_connect').should('not.exist');
		cy.get('input[value="Logout"]').click();

		// connect to "Mailchimp" Account button should be visible.
		cy.get('#mailchimp_sf_oauth_connect').should('exist');
	});
});
