/* eslint-disable no-undef */
describe('Block Tests', () => {
	let postId;

	before(() => {
		cy.login();
	});

	it('Admin can create a Signup form using Mailchimp block', () => {
		const postTitle = 'Mailchimp signup form - Block';
		const beforeSave = () => {
			cy.insertBlock('mailchimp/mailchimp', 'Mailchimp List Subscribe Form');
			cy.wait(500);
		};
		cy.createPost({ title: postTitle, content: '', beforeSave }).then((postBlock) => {
			if (postBlock) {
				postId = postBlock.id;
				cy.visit(`/?p=${postId}`);
				cy.get('#mc_signup').should('exist');
				cy.get('#mc_mv_EMAIL').should('exist');
				cy.get('#mc_signup_submit').should('exist');
				cy.get('#mc_signup_submit').click();
				cy.get('.mc_error_msg').should('exist');
				cy.get('.mc_error_msg').contains('Email Address: This value should not be blank.');
			}
		});
	});

	it('Admin can set header and sub-header in block', () => {
		cy.visit(`/wp-admin/post.php?post=${postId}&action=edit`);

		// Set content options
		const header = '[BLOCK] Subscribe to our newsletter';
		const subHeader =
			'[BLOCK] Join our mailing list to receive the latest news and updates from our team.';
		const button = 'Subscribe Now';
		cy.getBlockEditor().find('h2[aria-label="Enter a header (optional)"]').clear().type(header);
		cy.getBlockEditor()
			.find('h3[aria-label="Enter a sub header (optional)"]')
			.clear()
			.type(subHeader);
		cy.getBlockEditor().find('button[aria-label="Enter button text."]').clear().type(button);
		cy.get('button.editor-post-publish-button').click();

		// Verify
		cy.visit(`/?p=${postId}`);
		cy.get('.mc_custom_border_hdr').contains(header);
		cy.get('#mc_subheader').contains(subHeader);
		cy.get('#mc_signup_submit').contains(button);
	});

	it('Admin can re-order form fields in block', () => {
		cy.visit(`/wp-admin/post.php?post=${postId}&action=edit`);

		// Re-order email field
		cy.getBlockEditor().find('div[data-title="Email Field"]').click();
		cy.get('.block-editor-block-toolbar__block-controls').should('be.visible');

		cy.get('.block-editor-block-toolbar__block-controls')
			.find('button[aria-label="Move down"]')
			.click();
		cy.get('button.editor-post-publish-button').click();

		// Verify order of fields
		cy.visit(`/?p=${postId}`);
		cy.get('.mc_form_inside .wp-block-mailchimp-mailchimp-form-field:nth-child(3)').contains(
			'Email Address',
		);

		// Re-order email field
		cy.visit(`/wp-admin/post.php?post=${postId}&action=edit`);
		cy.getBlockEditor().find('div[data-title="Email Field"]').click();
		cy.get('.block-editor-block-toolbar__block-controls').should('be.visible');

		cy.get('.block-editor-block-toolbar__block-controls')
			.find('button[aria-label="Move up"]')
			.click();
		cy.get('button.editor-post-publish-button').click();

		// Verify order of fields
		cy.visit(`/?p=${postId}`);
		cy.get('.mc_form_inside .wp-block-mailchimp-mailchimp-form-field:nth-child(2)').contains(
			'Email Address',
		);
	});

	it('Admin can show/hide the form fields in block', () => {
		cy.visit(`/wp-admin/post.php?post=${postId}&action=edit`);

		// Hide First name field
		cy.getBlockEditor().find('label[for="FNAME"]').click();
		cy.get('.block-editor-block-toolbar__slot').should('be.visible');

		cy.get('.block-editor-block-toolbar__slot').find('button[aria-label="Visibility"]').click();
		cy.get('button.editor-post-publish-button').click();
		cy.wait(500);

		// Verify
		cy.visit(`/?p=${postId}`);
		cy.get('#mc_mv_FNAME').should('not.exist');

		// Show First name field
		cy.visit(`/wp-admin/post.php?post=${postId}&action=edit`);
		cy.getBlockEditor().find('div[data-title="Email Field"]').click();
		cy.getBlockEditor().find('label[for="FNAME"]').click();
		cy.get('.block-editor-block-toolbar__slot').should('be.visible');

		cy.get('.block-editor-block-toolbar__slot')
			.find('button[aria-label="Visibility"].is-pressed')
			.click();
		cy.get('button.editor-post-publish-button').click();
		cy.wait(500);

		// Verify
		cy.visit(`/?p=${postId}`);
		cy.get('#mc_mv_FNAME').should('exist');
	});

	it('Admin can show/hide groups from block settings', () => {
		// Show groups
		cy.visit(`/wp-admin/post.php?post=${postId}&action=edit`);
		cy.getBlockEditor().find('div[data-title="Email Field"]').click();
		cy.getBlockEditor().find('.mc_interests_header label').first().click();
		cy.get('.block-editor-block-toolbar__slot').should('be.visible');

		cy.get('.block-editor-block-toolbar__slot')
			.find('button[aria-label="Visibility"].is-pressed')
			.click();
		cy.get('button.editor-post-publish-button').click();
		cy.wait(500);

		// Verify
		cy.visit(`/?p=${postId}`);
		cy.get('.mc_interests_header').should('exist');
		cy.get('.mc_interest').should('exist');

		// Hide groups
		cy.visit(`/wp-admin/post.php?post=${postId}&action=edit`);
		cy.getBlockEditor().find('div[data-title="Email Field"]').click();
		cy.getBlockEditor().find('.mc_interests_header label').first().click();
		cy.get('.block-editor-block-toolbar__slot').should('be.visible');

		cy.get('.block-editor-block-toolbar__slot').find('button[aria-label="Visibility"]').click();
		cy.get('button.editor-post-publish-button').click();
		cy.wait(500);

		// Verify
		cy.visit(`/?p=${postId}`);
		cy.get('.mc_interests_header').should('not.exist');
		cy.get('.mc_interest').should('not.exist');
	});

	it('Admin can edit form field label in block', () => {
		// Show groups
		cy.visit(`/wp-admin/post.php?post=${postId}&action=edit`);
		const emailLabel = 'Email Address [EDITED]';
		cy.getBlockEditor().find('label[for="EMAIL"] label').clear().type(emailLabel);

		cy.get('button.editor-post-publish-button').click();
		cy.wait(500);

		// Verify
		cy.visit(`/?p=${postId}`);
		cy.get('#mc_mv_EMAIL').should('exist');
		cy.get('label[for="mc_mv_EMAIL"]').contains(emailLabel);
	});

	it('Admin can show/hide unsubscribe link from block settings', () => {
		// display unsubscribe link.
		cy.visit(`/wp-admin/post.php?post=${postId}&action=edit`);
		cy.getBlockEditor().find('h2[aria-label="Enter a header (optional)"]').click();
		cy.openDocumentSettingsPanel('Form Settings', 'Block');
		cy.get('.mailchimp-unsubscribe-link input.components-form-toggle__input').first().check();
		cy.get('button.editor-post-publish-button').click();
		cy.wait(500);

		// Verify
		cy.visit(`/?p=${postId}`);
		cy.get('#mc_unsub_link').should('exist');

		// Reset
		cy.visit(`/wp-admin/post.php?post=${postId}&action=edit`);
		cy.getBlockEditor().find('h2[aria-label="Enter a header (optional)"]').click();
		cy.openDocumentSettingsPanel('Form Settings', 'Block');
		cy.get('.mailchimp-unsubscribe-link input.components-form-toggle__input').first().uncheck();
		cy.get('button.editor-post-publish-button').click();
		cy.wait(500);

		// Verify
		cy.visit(`/?p=${postId}`);
		cy.get('#mc_unsub_link').should('not.exist');
	});

	it('Admin can change audience list from block settings', () => {
		// display unsubscribe link.
		cy.visit(`/wp-admin/post.php?post=${postId}&action=edit`);
		cy.getBlockEditor().find('h2[aria-label="Enter a header (optional)"]').click();
		cy.openDocumentSettingsPanel('Settings', 'Block');
		cy.get('.mailchimp-list-select select').select('Alternate 10up Audience');
		cy.wait(2000);
		cy.getBlockEditor().find('label[for="EMAIL"] label').contains('Email Address');
		cy.getBlockEditor().find('label[for="MMERGE9"]').should('not.exist');

		cy.get('button.editor-post-publish-button').click();
		cy.wait(500);

		// Verify
		cy.visit(`/?p=${postId}`);
		cy.get('#mc_signup').should('exist');
		cy.get('#mc_mv_EMAIL').should('exist');
		cy.get('#mc_signup_submit').should('exist');

		// Reset
		cy.visit(`/wp-admin/post.php?post=${postId}&action=edit`);
		cy.getBlockEditor().find('h2[aria-label="Enter a header (optional)"]').click();
		cy.openDocumentSettingsPanel('Settings', 'Block');
		cy.get('.mailchimp-list-select select').select('10up');
		cy.wait(2000);
		cy.getBlockEditor().find('label[for="MMERGE9"]').should('exist');
		cy.get('button.editor-post-publish-button').click();
		cy.wait(500);
	});

	it('[Backward Compatibility] Admin can see settings for the existing old block', () => {
		cy.wpCli(
			`wp post create --post_title='OLD BLOCK' --post_content='<!-- wp:mailchimp/mailchimp -->' --post_status='publish' --porcelain`,
		).then((response) => {
			const oldBlockPostId = response.stdout;
			cy.visit(`/?p=${oldBlockPostId}`);
			cy.get('#mc_signup').should('exist');
			cy.get('#mc_mv_EMAIL').should('exist');
			cy.get('#mc_signup_submit').should('exist');

			cy.visit(`/wp-admin/post.php?post=${oldBlockPostId}&action=edit`);
			const header = '[NEW BLOCK] Subscribe to our newsletter';
			cy.getBlockEditor()
				.find('h2[aria-label="Enter a header (optional)"]')
				.clear()
				.type(header);
			cy.get('button.editor-post-publish-button').click();
			cy.wait(500);

			// Verify
			cy.visit(`/?p=${oldBlockPostId}`);
			cy.get('.mc_custom_border_hdr').contains(header);
		});
	});

	// TODO: Add tests for the Double Opt-in and Update existing subscribers settings.
	// TODO: Add tests for the block styles settings.
	// TODO: Add tests for the form submission.
});
