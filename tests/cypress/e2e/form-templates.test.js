/* eslint-disable no-undef */
describe('Form Templates Tests', () => {
	before(() => {
		cy.login();
		cy.mailchimpLoginIfNotAlreadyLoggedIn();
		cy.toggleMergeFields('check');

		// Hide all interest groups
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('input[id^="mc_show_interest_groups_"]').uncheck();
		cy.get('input[id^="mc_show_interest_groups_"]').trigger('change');
		cy.get('input[value="Save Changes"]:visible').first().click();
	});

	it('Admin should see the form templates in the block', () => {
		const postTitle = 'Mailchimp signup form - Form Templates';
		const beforeSave = () => {
			cy.insertBlock('mailchimp/mailchimp', 'Mailchimp List Subscribe Form').then(
				(blockId) => {
					cy.getBlockEditor()
						.find(`#${blockId} ul.block-editor-block-variation-picker__variations li`)
						.should('have.length', 4);
					cy.getBlockEditor()
						.find(
							`#${blockId} ul li:nth-child(1) span.block-editor-block-variation-picker__variation-label`,
						)
						.should('have.text', 'Quick Signup (Email Only)');
					cy.getBlockEditor()
						.find(
							`#${blockId} ul li:nth-child(2) span.block-editor-block-variation-picker__variation-label`,
						)
						.should('have.text', 'Personal Signup (Name and Email)');
					cy.getBlockEditor()
						.find(
							`#${blockId} ul li:nth-child(3) span.block-editor-block-variation-picker__variation-label`,
						)
						.should('have.text', 'Contact Form (Contact Details)');
					cy.getBlockEditor()
						.find(
							`#${blockId} ul li:nth-child(4) span.block-editor-block-variation-picker__variation-label`,
						)
						.should('have.text', 'Default Form (All Fields)');
				},
			);
			cy.wait(500);
		};
		cy.createPost({ title: postTitle, content: '', beforeSave });
	});

	const beforeSave = (variation) => {
		cy.insertBlock('mailchimp/mailchimp', 'Mailchimp List Subscribe Form').then((blockId) => {
			cy.getBlockEditor()
				.find(
					`#${blockId} ul.block-editor-block-variation-picker__variations li:nth-child(${variation})`,
				)
				.click();
			cy.getBlockEditor()
				.find('h2[aria-label="Enter a header (optional)"]')
				.should('be.visible');
		});
		cy.wait(500);
	};

	it('Admin can select a form template (Quick Signup)', () => {
		const postTitle = 'Mailchimp signup form - Form Template 1';

		cy.createPost({ title: postTitle, content: '', beforeSave: () => beforeSave(1) }).then(
			(postBlock) => {
				if (postBlock) {
					cy.visit(`/?p=${postBlock.id}`);
					cy.get('input[id^="mc_mv_EMAIL"]').should('exist');
					cy.get('.mc_signup_submit_button').should('exist');
					cy.get('input[id^="mc_mv_FNAME"]').should('not.exist');
					cy.get('input[id^="mc_mv_LNAME"]').should('not.exist');
					cy.get('input[id^="mc_mv_PHONE"]').should('not.exist');
				}
			},
		);
	});

	it('Admin can select a form template (Personal Signup)', () => {
		const postTitle = 'Mailchimp signup form - Form Template 2';
		cy.createPost({ title: postTitle, content: '', beforeSave: () => beforeSave(2) }).then(
			(postBlock2) => {
				if (postBlock2) {
					cy.visit(`/?p=${postBlock2.id}`);
					cy.get('input[id^="mc_mv_EMAIL"]').should('exist');
					cy.get('.mc_signup_submit_button').should('exist');
					cy.get('input[id^="mc_mv_FNAME"]').should('exist');
					cy.get('input[id^="mc_mv_LNAME"]').should('exist');
					cy.get('input[id^="mc_mv_PHONE"]').should('not.exist');
				}
			},
		);
	});

	it('Admin can select a form template (Contact Form)', () => {
		const postTitle = 'Mailchimp signup form - Form Template 3';
		cy.createPost({ title: postTitle, content: '', beforeSave: () => beforeSave(3) }).then(
			(postBlock3) => {
				if (postBlock3) {
					cy.visit(`/?p=${postBlock3.id}`);
					cy.get('input[id^="mc_mv_EMAIL"]').should('exist');
					cy.get('.mc_signup_submit_button').should('exist');
					cy.get('input[id^="mc_mv_FNAME"]').should('exist');
					cy.get('input[id^="mc_mv_LNAME"]').should('exist');
					cy.get('input[id^="mc_mv_PHONE"]').should('exist');
					cy.get('input[name="mc_mv_ADDRESS[addr1]"]').should('exist');
					cy.get('input[name="mc_mv_ADDRESS[addr2]"]').should('exist');
					cy.get('input[name="mc_mv_ADDRESS[city]"]').should('exist');
				}
			},
		);
	});

	it('Admin can select a form template (Default Form)', () => {
		const postTitle = 'Mailchimp signup form - Form Template 4';
		cy.createPost({ title: postTitle, content: '', beforeSave: () => beforeSave(4) }).then(
			(postBlock4) => {
				if (postBlock4) {
					cy.visit(`/?p=${postBlock4.id}`);
					cy.get('input[id^="mc_mv_EMAIL"]').should('exist');
					cy.get('.mc_signup_submit_button').should('exist');
					cy.get('input[id^="mc_mv_FNAME"]').should('exist');
					cy.get('input[id^="mc_mv_LNAME"]').should('exist');
					cy.get('input[id^="mc_mv_PHONE"]').should('exist');
					cy.get('input[id^="mc_mv_COMPANY"]').should('exist');
					cy.get('input[name="mc_mv_ADDRESS[addr1]"]').should('exist');
					cy.get('input[name="mc_mv_ADDRESS[addr2]"]').should('exist');
				}
			},
		);
	});
});
