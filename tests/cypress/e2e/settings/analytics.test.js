/* eslint-disable no-undef */
describe('Analytics admin page', () => {
	before(() => {
		cy.login();
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
			cy.get('#wpbody h1.mailchimp-sf-settings-page-hero-title').contains('Analytics');
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
