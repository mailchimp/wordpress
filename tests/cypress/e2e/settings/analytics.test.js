/* eslint-disable no-undef */
describe('Analytics admin page', () => {
	before(() => {
		cy.login();
		cy.wpCli('wp option update date_format "Y-m-d"');
	});

	describe('When connected', () => {
		it('Can see "Analytics" submenu under Mailchimp menu', () => {
			cy.visit('/wp-admin/');
			cy.get('#adminmenu li#toplevel_page_mailchimp_sf_options').click();
			cy.get('#adminmenu li#toplevel_page_mailchimp_sf_options .wp-submenu')
				.contains('Analytics')
				.should('be.visible');
		});

		it('Can visit Analytics page and see the heading', () => {
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_analytics');
			cy.get('#wpbody h1.mailchimp-sf-settings-page-header-title').contains('Analytics');
		});

		it('Analytics page loads required assets', () => {
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_analytics');

			cy.get('script[id="mailchimp_sf_chartjs-js"]').should('exist');
			cy.get('script[id="mailchimp_sf_analytics_js-js"]').should('exist');
			cy.get('link[id="mailchimp_sf_analytics_css-css"]').should('exist');
		});

		it('Assets do not load on other admin pages', () => {
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

			cy.get('script[id="mailchimp_sf_chartjs-js"]').should('not.exist');
			cy.get('script[id="mailchimp_sf_analytics_js-js"]').should('not.exist');
			cy.get('link[id="mailchimp_sf_analytics_css-css"]').should('not.exist');
		});

		it('Date picker trigger shows "Last 30 days" by default', () => {
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_analytics');
			cy.get('#mailchimp-sf-date-picker-label').should('have.text', 'Last 30 days');
		});

		it('Date picker popover is hidden by default', () => {
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_analytics');
			cy.get('#mailchimp-sf-date-picker-popover').should('not.be.visible');
		});

		it('Clicking the trigger opens the date picker popover', () => {
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_analytics');
			cy.get('#mailchimp-sf-date-picker-trigger').click();
			cy.get('#mailchimp-sf-date-picker-popover').should('be.visible');
			cy.get('#mailchimp-sf-date-range').should('be.visible');
			cy.get('#mailchimp-sf-date-from').should('be.visible');
			cy.get('#mailchimp-sf-date-to').should('be.visible');
		});

		it('Cancel button closes the popover without applying', () => {
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_analytics');
			cy.get('#mailchimp-sf-date-picker-trigger').click();
			cy.get('#mailchimp-sf-date-range').select('7');
			cy.get('#mailchimp-sf-date-picker-cancel').click();
			cy.get('#mailchimp-sf-date-picker-popover').should('not.be.visible');
			cy.get('#mailchimp-sf-date-picker-label').should('have.text', 'Last 30 days');
		});

		it('Apply button updates the trigger label and closes the popover', () => {
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_analytics');
			cy.get('#mailchimp-sf-date-picker-trigger').click();
			cy.get('#mailchimp-sf-date-range').select('7');
			cy.get('#mailchimp-sf-date-picker-apply').click();
			cy.get('#mailchimp-sf-date-picker-popover').should('not.be.visible');
			cy.get('#mailchimp-sf-date-picker-label').should('have.text', 'Last 7 days');
		});

		it('Selecting Last 7 days updates start and end inputs to an inclusive 7-day range', () => {
			cy.clock(new Date(2026, 3, 2).getTime(), ['Date']);
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_analytics');
			cy.get('#mailchimp-sf-date-picker-trigger').click();
			cy.get('#mailchimp-sf-date-range').select('7');
			cy.get('#mailchimp-sf-date-from').should('have.value', '2026-03-27');
			cy.get('#mailchimp-sf-date-to').should('have.value', '2026-04-02');
		});

		it('Editing dates to a non-matching range sets the preset dropdown to Custom', () => {
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_analytics');
			cy.get('#mailchimp-sf-date-picker-trigger').click();
			cy.get('#mailchimp-sf-date-from').invoke('val', '2000-01-01');
			cy.get('#mailchimp-sf-date-to').invoke('val', '2000-01-31').trigger('change');
			cy.get('#mailchimp-sf-date-range').should('have.value', 'custom');
		});

		it('Date picker trigger reflects aria-expanded when the popover opens and closes', () => {
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_analytics');
			cy.get('#mailchimp-sf-date-picker-trigger').should(
				'have.attr',
				'aria-expanded',
				'false',
			);
			cy.get('#mailchimp-sf-date-picker-trigger').click();
			cy.get('#mailchimp-sf-date-picker-trigger').should(
				'have.attr',
				'aria-expanded',
				'true',
			);
			cy.get('#mailchimp-sf-date-picker-cancel').click();
			cy.get('#mailchimp-sf-date-picker-trigger').should(
				'have.attr',
				'aria-expanded',
				'false',
			);
		});

		it('Clicking outside the popover closes it', () => {
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_analytics');
			cy.get('#mailchimp-sf-date-picker-trigger').click();
			cy.get('#mailchimp-sf-date-picker-popover').should('be.visible');
			cy.get('body').click(0, 0);
			cy.get('#mailchimp-sf-date-picker-popover').should('not.be.visible');
		});

		it('List filter is present with options', () => {
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_analytics');
			cy.get('#mailchimp-sf-list-filter').should('exist');
			cy.get('#mailchimp-sf-list-filter option').should('have.length.greaterThan', 0);
		});
	});

	describe('When not connected', () => {
		before(() => {
			cy.login();
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
			cy.mailchimpLogout();
		});

		it('Analytics submenu is not visible', () => {
			cy.visit('/wp-admin/');
			cy.get('#adminmenu li#toplevel_page_mailchimp_sf_options').click();
			cy.get('#adminmenu li#toplevel_page_mailchimp_sf_options .wp-submenu')
				.should('not.contain', 'Analytics');
		});

		after(() => {
			cy.login();
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
			cy.mailchimpLoginIfNotAlreadyLoggedIn();
		});
	});
});
