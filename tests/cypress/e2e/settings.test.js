/* eslint-disable no-undef */
describe('Admin can update plugin settings', () => {
	let shortcodePostId = 0;
	let blockPostId = 0;

	before(() => {
		cy.login();
	});

	it('Admin can see list and save it', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		cy.get('.mc-h2').contains('Your Lists');
		cy.get('#mc_list_id').select('10up');
		cy.get('input[value="Update List"]').click();
		cy.get('#mc-message .success_msg b').contains('Success!');
	});

	it('Admin can create a Signup form using the shortcode', () => {
		const postTitle = 'Mailchimp signup form - shortcode';
		const beforeSave = () => {
			cy.insertBlock('core/shortcode').then((id) => {
				cy.get(`#${id} .blocks-shortcode__textarea`).type('[mailchimpsf_form]');
			});
		};
		cy.createPost({ title: postTitle, content: '', beforeSave }).then((post) => {
			if (post) {
				shortcodePostId = post.id;
				cy.visit(`/?p=${shortcodePostId}`);
				cy.get('#mc_signup').should('exist');
				cy.get('#mc_mv_EMAIL').should('exist');
				cy.get('#mc_signup_submit').should('exist');
				cy.get('#mc_signup_submit').click();
				cy.get('.mc_error_msg').should('exist');
				cy.get('.mc_error_msg').contains(': This value should not be blank.');
			}
		});
	});

	it('Admin can create a Signup form using Mailchimp block', () => {
		const postTitle = 'Mailchimp signup form - Block';
		const beforeSave = () => {
			cy.insertBlock('mailchimp/mailchimp');
		};
		cy.createPost({ title: postTitle, content: '', beforeSave }).then((post) => {
			if (post) {
				blockPostId = post.id;
				cy.visit(`/?p=${shortcodePostId}`);
				cy.get('#mc_signup').should('exist');
				cy.get('#mc_mv_EMAIL').should('exist');
				cy.get('#mc_signup_submit').should('exist');
				cy.get('#mc_signup_submit').click();
				cy.get('.mc_error_msg').should('exist');
				cy.get('.mc_error_msg').contains(': This value should not be blank.');
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
		cy.visit(`/?p=${shortcodePostId}`);
		cy.get('.mc_custom_border_hdr').contains(header);
		cy.get('#mc_subheader').contains(subHeader);
		cy.get('#mc_signup_submit').contains(button);

		cy.visit(`/?p=${blockPostId}`);
		cy.get('.mc_custom_border_hdr').contains(header);
		cy.get('#mc_subheader').contains(subHeader);
		cy.get('#mc_signup_submit').contains(button);
	});

	it('Admin can remove mailchimp CSS', () => {
		// Remove mailchimp CSS.
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_nuke_all_styles').check();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		cy.visit(`/?p=${shortcodePostId}`);
		cy.get('#mc_subheader').should('not.have.css', 'margin-bottom', '18px');

		cy.visit(`/?p=${blockPostId}`);
		cy.get('#mc_subheader').should('not.have.css', 'margin-bottom', '18px');

		// Enable mailchimp CSS.
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_nuke_all_styles').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		cy.visit(`/?p=${shortcodePostId}`);
		cy.get('#mc_subheader').should('have.css', 'margin-bottom', '18px');

		cy.visit(`/?p=${blockPostId}`);
		cy.get('#mc_subheader').should('have.css', 'margin-bottom', '18px');
	});

	it('Admin can set custom styling on signup form', () => {
		// Remove mailchimp CSS.
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_custom_style').check();
		cy.get('#mc_form_border_width').clear().type('10');
		cy.get('#mc_form_border_color').clear().type('000000');
		cy.get('#mc_form_text_color').clear().type('FF0000');
		cy.get('#mc_form_background').clear().type('00FF00');
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();

		// Verify
		cy.visit(`/?p=${shortcodePostId}`);
		cy.get('#mc_signup form').should('have.css', 'border-width', '10px');
		cy.get('#mc_signup form').should('have.css', 'border-color', 'rgb(0, 0, 0)');
		cy.get('#mc_signup form').should('have.css', 'color', 'rgb(255, 0, 0)');
		cy.get('#mc_signup form').should('have.css', 'background-color', 'rgb(0, 255, 0)');

		cy.visit(`/?p=${blockPostId}`);
		cy.get('#mc_signup form').should('have.css', 'border-width', '10px');
		cy.get('#mc_signup form').should('have.css', 'border-color', 'rgb(0, 0, 0)');
		cy.get('#mc_signup form').should('have.css', 'color', 'rgb(255, 0, 0)');
		cy.get('#mc_signup form').should('have.css', 'background-color', 'rgb(0, 255, 0)');

		// Enable mailchimp CSS.
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#mc_custom_style').uncheck();
		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	});
});
