const { generateRandomEmail } = require('../support/functions/utility');

/* eslint-disable no-undef */
describe('User Sync Tests', () => {
	before(() => {
		cy.login();
		cy.mailchimpLoginIfNotAlreadyLoggedIn();
	});

	it('Admin can see User Sync settings', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('.mailchimp-sf-user-sync-page').should('be.visible');
		cy.get('.mailchimp-sf-user-sync-page h2').first().should('contain', 'User sync settings');
	});

	it('Admin can save User Sync settings', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Enable auto user sync
		cy.get('#enable_user_sync').check({ force: true });

		// Select subscriber role
		cy.get('input[name="mailchimp_sf_user_sync_settings[user_roles][subscriber]"]').check();

		// Select subscriber status
		cy.get(
			'input[name="mailchimp_sf_user_sync_settings[subscriber_status]"][value="subscribed"]',
		).check();

		// Save settings
		cy.get('#existing_contacts_only').trigger('change');
		cy.get('#mailchimp_sf_user_sync_settings_submit').click();

		// Verify success message
		cy.get('.notice-success').should('be.visible');
	});

	['subscribed', 'pending', 'transactional'].forEach((status) => {
		it(`[${status}] Admin can start user sync and validate sync results`, () => {
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
			cy.get('#enable_user_sync').uncheck({ force: true });
			cy.get('#existing_contacts_only').trigger('change');
			cy.get('#mailchimp_sf_user_sync_settings_submit').click();

			cy.deleteWPSubscriberUser();

			const email = generateRandomEmail('user-sync-test');
			const firstName = `First${Date.now()}`;
			const lastName = `Last${Date.now()}`;
			cy.wpCli(
				`wp user create ${email} ${email} --role=subscriber --first_name=${firstName} --last_name=${lastName}`,
			);

			// Select subscriber role
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
			cy.get(
				'input[name="mailchimp_sf_user_sync_settings[existing_contacts_only]"]',
			).uncheck();
			// Select subscriber status
			cy.get(
				`input[name="mailchimp_sf_user_sync_settings[subscriber_status]"][value="${status}"]`,
			).check();
			cy.get('.mailchimp-user-sync-user-roles input[type="checkbox"]').uncheck();
			cy.get('input[name="mailchimp_sf_user_sync_settings[user_roles][subscriber]"]').check();
			cy.get('#existing_contacts_only').trigger('change');
			cy.get('#mailchimp_sf_user_sync_settings_submit').click();

			// Start sync
			cy.get('a.mailchimp-sf-button.mailchimp-sf-button-submit.btn-primary')
				.contains('Manual Sync')
				.click();

			// Verify sync started
			cy.get('.mailchimp-sf-sync-progress').should('be.visible');
			cy.get('.sync-status-text').should('contain', 'Syncing users');

			const checkSyncStatus = (attempts = 0) => {
				if (attempts >= 9) return;

				cy.wait(10000);
				cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
				cy.get('body').then(($body) => {
					if ($body.find('.mailchimp-sf-sync-progress').length === 0) {
						return;
					}
					checkSyncStatus(attempts + 1);
				});
			};

			checkSyncStatus();

			// Verify success message
			cy.get('.notice-success').should('be.visible');
			cy.get('.notice-success').should('contain', 'User sync process completed.');
			cy.get('.notice-success').should('contain', 'Synced: 1');

			// Verify user sync status
			cy.verifyContactInMailchimp(email).then((response) => {
				cy.wrap(response.status).should('eq', status);
			});

			cy.deleteContactFromList(email);
		});
	});

	it('Admin can sync existing contacts only', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#enable_user_sync').uncheck({ force: true });
		cy.get('#existing_contacts_only').trigger('change');
		cy.get('#mailchimp_sf_user_sync_settings_submit').click();

		cy.deleteWPSubscriberUser();

		const email = generateRandomEmail('user-sync-test');
		const firstName = `First${Date.now()}`;
		const lastName = `Last${Date.now()}`;
		cy.wpCli('wp user create opensource opensource@10up.com --role=subscriber');
		cy.wpCli(
			`wp user create ${email} ${email} --role=subscriber --first_name=${firstName} --last_name=${lastName}`,
		);

		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		// Enable existing contacts only
		cy.get('input[name="mailchimp_sf_user_sync_settings[existing_contacts_only]"]').check();
		cy.get('#existing_contacts_only').trigger('change');
		cy.get('#mailchimp_sf_user_sync_settings_submit').click();
		cy.get('.notice-success').should('be.visible');

		// Start sync
		cy.get('a.mailchimp-sf-button.mailchimp-sf-button-submit.btn-primary')
			.contains('Manual Sync')
			.click();

		// Verify sync started
		cy.get('.mailchimp-sf-sync-progress').should('be.visible');
		cy.get('.sync-status-text').should('contain', 'Syncing users');

		const checkSyncStatus = (attempts = 0) => {
			if (attempts >= 9) return;

			cy.wait(10000);
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
			cy.get('body').then(($body) => {
				if ($body.find('.mailchimp-sf-sync-progress').length === 0) {
					return;
				}
				checkSyncStatus(attempts + 1);
			});
		};

		checkSyncStatus();

		// Verify success message
		cy.get('.notice-success').should('be.visible');
		cy.get('.notice-success').should('contain', 'User sync process completed.');
		cy.get('.notice-success').should('contain', 'Synced: 1');
		cy.get('.notice-success').should('contain', 'Skipped: 1');

		cy.deleteWPSubscriberUser();
	});

	it('Admin can see error logs of user sync and delete specific error log', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#enable_user_sync').uncheck({ force: true });
		cy.get('#existing_contacts_only').trigger('change');
		cy.get('.mailchimp-user-sync-user-roles input[type="checkbox"]').uncheck();
		cy.get('input[name="mailchimp_sf_user_sync_settings[existing_contacts_only]"]').uncheck();
		cy.get('input[name="mailchimp_sf_user_sync_settings[user_roles][administrator]"]').check();
		cy.get('#mailchimp_sf_user_sync_settings_submit').click();

		// Start sync
		cy.get('a.mailchimp-sf-button.mailchimp-sf-button-submit.btn-primary')
			.contains('Manual Sync')
			.click();

		// Verify sync started
		cy.get('.mailchimp-sf-sync-progress').should('be.visible');
		cy.get('.sync-status-text').should('contain', 'Syncing users');

		const checkSyncStatus = (attempts = 0) => {
			if (attempts >= 9) return;

			cy.wait(10000);
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
			cy.get('body').then(($body) => {
				if ($body.find('.mailchimp-sf-sync-progress').length === 0) {
					return;
				}
				checkSyncStatus(attempts + 1);
			});
		};

		checkSyncStatus();

		// Verify success message
		cy.get('.notice-success').should('be.visible');
		cy.get('.notice-success').should('contain', 'User sync process completed.');
		cy.get('.notice-success').should('contain', 'Failed: 1');

		// Verify error logs section
		cy.get('.mailchimp-sf-user-sync-errors').should('be.visible');
		cy.get(
			'.mailchimp-user-sync-user-sync-errors .mailchimp-sf-user-sync-settings-field-label',
		).should('contain', 'User Sync Errors');

		// Verify error log
		cy.get('.mailchimp-sf-user-sync-errors-table tbody tr').should('have.length', 1);
		cy.get('.mailchimp-sf-user-sync-errors-table tbody tr').should(
			'contain',
			'wordpress@example.com',
		);

		// Delete specific error
		cy.get('.mailchimp-sf-user-sync-error-delete').first().click();

		// Verify errors are cleared
		cy.get('.mailchimp-sf-user-sync-errors-table tbody tr').should(
			'contain',
			'No errors found',
		);
	});

	it('Admin can cancel inprogress user sync', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		// Start sync
		cy.get('a.mailchimp-sf-button.mailchimp-sf-button-submit.btn-primary')
			.contains('Manual Sync')
			.click();

		// Cancel sync
		cy.get('.mailchimp-cancel-user-sync-button').click();

		// Verify cancel message
		cy.get('.notice-success').should('contain', 'User sync process will be cancelled soon.');
	});

	it('New user and user update should sync to Mailchimp', () => {
		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
		cy.get('#enable_user_sync').check({ force: true });
		cy.get('#existing_contacts_only').trigger('change');
		cy.get('.mailchimp-user-sync-user-roles input[type="checkbox"]').uncheck();
		cy.get('input[name="mailchimp_sf_user_sync_settings[existing_contacts_only]"]').uncheck();
		cy.get('input[name="mailchimp_sf_user_sync_settings[user_roles][subscriber]"]').check();
		cy.get('#mailchimp_sf_user_sync_settings_submit').click();

		cy.deleteWPSubscriberUser();
		const email = generateRandomEmail('user-sync-test2');

		// Create a test user first
		cy.wpCli(`wp user create ${email} ${email} --role=subscriber`);

		const checkSyncStatus = (attempts = 0) => {
			if (attempts >= 9) return;

			cy.wait(10000);
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
			cy.getContactInMailchimp(email).then((res) => {
				if (res && res.id) {
					return;
				}
				checkSyncStatus(attempts + 1);
			});
		};

		// Wait for sync to complete, as it happens in the background
		checkSyncStatus();

		// Update user and validate sync
		const firstName = `First${Date.now()}`;
		const lastName = `Last${Date.now()}`;
		cy.wpCli(`wp user update ${email} --first_name=${firstName} --last_name=${lastName}`);

		const checkSyncStatus2 = (attempts = 0) => {
			if (attempts >= 9) return;

			cy.wait(10000);
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
			cy.getContactInMailchimp(email).then((res) => {
				if (res && res.merge_fields?.FNAME) {
					cy.wrap(res.merge_fields?.FNAME).should('eq', firstName);
					cy.wrap(res.merge_fields?.LNAME).should('eq', lastName);
				}
				checkSyncStatus(attempts + 1);
			});
		};

		// Wait for sync to complete, as it happens in the background
		checkSyncStatus2();

		// Remove contact from Mailchimp
		cy.deleteContactFromList(email);
	});
});
