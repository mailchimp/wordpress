/* eslint-disable no-undef */
const {
	isSubscriberActivityRequest,
	buildSuccessData,
	wpJsonSuccess,
	wpJsonError,
} = require('../../support/functions/subscriberActivityAjax');
const {
	isFormPerformanceRequest,
	buildSuccessData: buildFormPerformanceData,
	wpJsonSuccess: wpJsonSuccessFp,
	wpJsonError: wpJsonErrorFp,
} = require('../../support/functions/formPerformanceAjax');

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

		describe('Subscriber activity chart (stubbed)', () => {
			const analyticsUrl = '/wp-admin/admin.php?page=mailchimp_sf_analytics';

			function stubSubscriberActivity(replyFn) {
				cy.intercept('POST', '**/admin-ajax.php', (req) => {
					if (!isSubscriberActivityRequest(req)) {
						req.continue();
						return;
					}
					const payload = typeof replyFn === 'function' ? replyFn(req) : replyFn;
					req.reply({
						statusCode: 200,
						headers: { 'content-type': 'application/json; charset=UTF-8' },
						body: payload,
					});
				}).as('subscriberActivity');
			}

			it('Subscriber activity section shell (headings, canvases)', () => {
				stubSubscriberActivity(() => wpJsonSuccess(buildSuccessData()));
				cy.visit(analyticsUrl);
				cy.wait('@subscriberActivity');
				cy.get('[data-section="subscriber-activity"]').should('be.visible');
				cy.get('#mailchimp-sf-sa-title').contains('Subscriber change over time');
				cy.get('#mailchimp-sf-sa-totals-title').contains(
					'Totals for the selected date range',
				);
				cy.get('#mailchimp-sf-sa-bar').should('exist');
				cy.get('#mailchimp-sf-sa-donut').should('exist');
			});

			it('Success payload shows ready state and numeric totals', () => {
				stubSubscriberActivity(() =>
					wpJsonSuccess(
						buildSuccessData({
							total_new: 12,
							total_unsubs: 4,
							net_change: 8,
							data: [
								{
									key: '2026-04-01',
									label: 'Apr 1',
									new_subscribers: 12,
									unsubscribes: 4,
								},
							],
						}),
					),
				);
				cy.visit(analyticsUrl);
				cy.wait('@subscriberActivity');
				cy.get('[data-section="subscriber-activity"]').should('have.class', 'is-ready');
				cy.get('[data-section="subscriber-activity"]').should(
					'not.have.class',
					'is-loading',
				);
				cy.get('[data-section="subscriber-activity"]').should('not.have.class', 'is-error');
				cy.get('#mailchimp-sf-sa-net').should('contain', '+8');
				cy.get('#mailchimp-sf-sa-total-new').should('have.text', '12');
				cy.get('#mailchimp-sf-sa-total-unsubs').should('have.text', '4');
			});

			it('Empty data shows empty state', () => {
				stubSubscriberActivity(() =>
					wpJsonSuccess(
						buildSuccessData({
							data: [],
							total_new: 0,
							total_unsubs: 0,
							net_change: 0,
						}),
					),
				);
				cy.visit(analyticsUrl);
				cy.wait('@subscriberActivity');
				cy.get('[data-section="subscriber-activity"]').should('have.class', 'is-empty');
				cy.get('#mailchimp-sf-sa-daterange').contains(
					'No data available for the selected date range',
				);
				cy.get('#mailchimp-sf-sa-overlay').contains(
					'No data available for this date range',
				);
			});

			it('API error shows error banner', () => {
				stubSubscriberActivity(() => wpJsonError('Stub API failure'));
				cy.visit(analyticsUrl);
				cy.wait('@subscriberActivity');
				cy.get('[data-section="subscriber-activity"]').should('have.class', 'is-error');
				cy.get('#mailchimp-sf-sa-error-banner').should('be.visible');
				cy.get('#mailchimp-sf-sa-error-message').contains('Stub API failure');
			});

			it('Retry after error loads success', () => {
				let n = 0;
				stubSubscriberActivity(() => {
					n += 1;
					if (n === 1) {
						return wpJsonError('First request fails');
					}
					return wpJsonSuccess(buildSuccessData());
				});
				cy.visit(analyticsUrl);
				cy.wait('@subscriberActivity');
				cy.get('[data-section="subscriber-activity"]').should('have.class', 'is-error');
				cy.get('#mailchimp-sf-sa-error-retry').click();
				cy.wait('@subscriberActivity');
				cy.get('[data-section="subscriber-activity"]').should('have.class', 'is-ready');
				cy.get('#mailchimp-sf-sa-error-banner').should('have.attr', 'hidden');
			});

			it('Limited range shows notice', () => {
				stubSubscriberActivity(() =>
					wpJsonSuccess(
						buildSuccessData({
							limited: true,
						}),
					),
				);
				cy.visit(analyticsUrl);
				cy.wait('@subscriberActivity');
				cy.get('#mailchimp-sf-sa-notice')
					.should('be.visible')
					.and(
						'contain',
						'Mailchimp subscriber activity is only available for the last 180 days. Showing available data.',
					);
			});

			it('Changing list filter triggers another subscriber activity request', function () {
				stubSubscriberActivity(() => wpJsonSuccess(buildSuccessData()));
				cy.visit(analyticsUrl);
				cy.wait('@subscriberActivity');
				cy.get('#mailchimp-sf-list-filter option').then(function ($options) {
					const values = [...$options].map((o) => o.value).filter(Boolean);
					if (values.length < 2) {
						this.skip();
					}
					cy.get('#mailchimp-sf-list-filter').select(values[1]);
					cy.wait('@subscriberActivity');
				});
			});

			it('Applying a different date preset triggers another subscriber activity request', () => {
				stubSubscriberActivity(() => wpJsonSuccess(buildSuccessData()));
				cy.visit(analyticsUrl);
				cy.wait('@subscriberActivity');
				cy.get('#mailchimp-sf-date-picker-trigger').click();
				cy.get('#mailchimp-sf-date-range').select('7');
				cy.get('#mailchimp-sf-date-picker-apply').click();
				cy.wait('@subscriberActivity');
				cy.get('#mailchimp-sf-date-picker-label').should('have.text', 'Last 7 days');
			});
		});

		describe('Form performance chart (stubbed)', () => {
			const analyticsUrl = '/wp-admin/admin.php?page=mailchimp_sf_analytics';

			// The Analytics page always fires both AJAX actions on refresh.
			function stubFormPerformance(replyFn) {
				cy.intercept('POST', '**/admin-ajax.php', (req) => {
					if (isFormPerformanceRequest(req)) {
						const payload = typeof replyFn === 'function' ? replyFn(req) : replyFn;
						req.reply({
							statusCode: 200,
							headers: { 'content-type': 'application/json; charset=UTF-8' },
							body: payload,
						});
						return;
					}
					if (isSubscriberActivityRequest(req)) {
						req.reply({
							statusCode: 200,
							headers: { 'content-type': 'application/json; charset=UTF-8' },
							body: wpJsonSuccess(
								buildSuccessData({
									data: [],
									total_new: 0,
									total_unsubs: 0,
									net_change: 0,
								}),
							),
						});
						return;
					}
					req.continue();
				}).as('formPerformance');
			}

			it('Form performance section shell (title, subtitle, canvas)', () => {
				stubFormPerformance(() => wpJsonSuccessFp(buildFormPerformanceData()));
				cy.visit(analyticsUrl);
				cy.wait('@formPerformance');
				cy.get('[data-section="form-performance"]').should('be.visible');
				cy.get('#mailchimp-sf-fp-title').contains('Forms performance over time');
				cy.get('.mailchimp-sf-fp__chart-title').contains('Form Activity');
				cy.get('#mailchimp-sf-fp-line').should('exist');
			});

			it('Success payload shows ready state', () => {
				stubFormPerformance(() =>
					wpJsonSuccessFp(
						buildFormPerformanceData({
							total_views: 500,
							total_submissions: 150,
							total_conversion_rate: 30.0,
							data: [
								{
									key: '2026-04-01',
									label: 'Apr 1',
									views: 500,
									submissions: 150,
									conversion_rate: 30.0,
								},
							],
						}),
					),
				);
				cy.visit(analyticsUrl);
				cy.wait('@formPerformance');
				cy.get('[data-section="form-performance"]').should('have.class', 'is-ready');
				cy.get('[data-section="form-performance"]').should(
					'not.have.class',
					'is-loading',
				);
				cy.get('[data-section="form-performance"]').should('not.have.class', 'is-error');
			});

			it('Empty data shows empty state', () => {
				stubFormPerformance(() =>
					wpJsonSuccessFp(
						buildFormPerformanceData({
							data: [],
							total_views: 0,
							total_submissions: 0,
							total_conversion_rate: 0,
						}),
					),
				);
				cy.visit(analyticsUrl);
				cy.wait('@formPerformance');
				cy.get('[data-section="form-performance"]').should('have.class', 'is-empty');
				cy.get('#mailchimp-sf-fp-daterange').contains(
					'No submissions recorded for the selected date range',
				);
				cy.get('#mailchimp-sf-fp-overlay').contains(
					'No data available for this date range',
				);
			});

			it('Zero views and zero submissions shows empty state even when rows exist', () => {
				stubFormPerformance(() =>
					wpJsonSuccessFp(
						buildFormPerformanceData({
							data: [
								{
									key: '2026-04-01',
									label: 'Apr 1',
									views: 0,
									submissions: 0,
									conversion_rate: 0,
								},
							],
							total_views: 0,
							total_submissions: 0,
							total_conversion_rate: 0,
						}),
					),
				);
				cy.visit(analyticsUrl);
				cy.wait('@formPerformance');
				cy.get('[data-section="form-performance"]').should('have.class', 'is-empty');
			});

			it('API error shows error banner', () => {
				stubFormPerformance(() => wpJsonErrorFp('Form performance stub failure'));
				cy.visit(analyticsUrl);
				cy.wait('@formPerformance');
				cy.get('[data-section="form-performance"]').should('have.class', 'is-error');
				cy.get('#mailchimp-sf-fp-error-banner').should('be.visible');
				cy.get('#mailchimp-sf-fp-error-message').contains('Form performance stub failure');
			});

			it('Retry after error loads success', () => {
				let n = 0;
				stubFormPerformance(() => {
					n += 1;
					if (n === 1) {
						return wpJsonErrorFp('First request fails');
					}
					return wpJsonSuccessFp(buildFormPerformanceData());
				});
				cy.visit(analyticsUrl);
				cy.wait('@formPerformance');
				cy.get('[data-section="form-performance"]').should('have.class', 'is-error');
				cy.get('#mailchimp-sf-fp-error-retry').click();
				cy.wait('@formPerformance');
				cy.get('[data-section="form-performance"]').should('have.class', 'is-ready');
				cy.get('#mailchimp-sf-fp-error-banner').should('have.attr', 'hidden');
			});

			it('Changing list filter triggers another form performance request', function () {
				stubFormPerformance(() => wpJsonSuccessFp(buildFormPerformanceData()));
				cy.visit(analyticsUrl);
				cy.wait('@formPerformance');
				cy.get('#mailchimp-sf-list-filter option').then(function ($options) {
					const values = [...$options].map((o) => o.value).filter(Boolean);
					if (values.length < 2) {
						this.skip();
					}
					cy.get('#mailchimp-sf-list-filter').select(values[1]);
					cy.wait('@formPerformance');
				});
			});

			it('Applying a different date preset triggers another form performance request', () => {
				stubFormPerformance(() => wpJsonSuccessFp(buildFormPerformanceData()));
				cy.visit(analyticsUrl);
				cy.wait('@formPerformance');
				cy.get('#mailchimp-sf-date-picker-trigger').click();
				cy.get('#mailchimp-sf-date-range').select('7');
				cy.get('#mailchimp-sf-date-picker-apply').click();
				cy.wait('@formPerformance');
				cy.get('#mailchimp-sf-date-picker-label').should('have.text', 'Last 7 days');
			});
		});

		describe('Audience Overview KPI block', () => {
			const analyticsUrl = '/wp-admin/admin.php?page=mailchimp_sf_analytics';

			function stubFormPerformance(replyFn) {
				cy.intercept('POST', '**/admin-ajax.php', (req) => {
					if (!isFormPerformanceRequest(req)) {
						req.continue();
						return;
					}
					const payload = typeof replyFn === 'function' ? replyFn(req) : replyFn;
					req.reply({
						statusCode: 200,
						headers: { 'content-type': 'application/json; charset=UTF-8' },
						body: payload,
					});
				}).as('formPerformance');
			}

			it('Audience Overview section shell (title, four KPIs)', () => {
				stubFormPerformance(() => wpJsonSuccessFp(buildFormPerformanceData()));
				cy.visit(analyticsUrl);
				cy.wait('@formPerformance');
				cy.get('[data-section="audience-overview"]').should('be.visible');
				cy.get('#mailchimp-sf-ao-title').contains('Audience Overview');
				cy.get('#mailchimp-sf-ao-total-subscribers').should('exist');
				cy.get('#mailchimp-sf-ao-views').should('exist');
				cy.get('#mailchimp-sf-ao-submissions').should('exist');
				cy.get('#mailchimp-sf-ao-rate').should('exist');
			});

			it('Success payload renders all four KPIs from the shared response', () => {
				stubFormPerformance(() =>
					wpJsonSuccessFp(
						buildFormPerformanceData({
							total_subscribers: 5082,
							total_views: 3138,
							total_submissions: 938,
							total_conversion_rate: 29.89,
						}),
					),
				);
				cy.visit(analyticsUrl);
				cy.wait('@formPerformance');
				cy.get('[data-section="audience-overview"]').should('have.class', 'is-ready');
				cy.get('#mailchimp-sf-ao-total-subscribers').should('contain', '5,082');
				cy.get('#mailchimp-sf-ao-views').should('contain', '3,138');
				cy.get('#mailchimp-sf-ao-submissions').should('contain', '938');
				cy.get('#mailchimp-sf-ao-rate').should('contain', '29.89%');
			});

			it('Missing total_subscribers (API failure) renders an em dash', () => {
				stubFormPerformance(() =>
					wpJsonSuccessFp(
						buildFormPerformanceData({
							total_subscribers: null,
							total_views: 100,
							total_submissions: 25,
							total_conversion_rate: 25.0,
						}),
					),
				);
				cy.visit(analyticsUrl);
				cy.wait('@formPerformance');
				cy.get('#mailchimp-sf-ao-total-subscribers').should('contain', '-');
				cy.get('#mailchimp-sf-ao-views').should('contain', '100');
			});

			it('API error shows error banner on the Audience Overview card', () => {
				stubFormPerformance(() => wpJsonErrorFp('Audience overview stub failure'));
				cy.visit(analyticsUrl);
				cy.wait('@formPerformance');
				cy.get('[data-section="audience-overview"]').should('have.class', 'is-error');
				cy.get('#mailchimp-sf-ao-error-banner').should('be.visible');
				cy.get('#mailchimp-sf-ao-error-message').contains('Audience overview stub failure');
			});

			it('Retry on Audience Overview triggers another shared request', () => {
				let n = 0;
				stubFormPerformance(() => {
					n += 1;
					if (n === 1) {
						return wpJsonErrorFp('First request fails');
					}
					return wpJsonSuccessFp(buildFormPerformanceData());
				});
				cy.visit(analyticsUrl);
				cy.wait('@formPerformance');
				cy.get('[data-section="audience-overview"]').should('have.class', 'is-error');
				cy.get('#mailchimp-sf-ao-error-retry').click();
				cy.wait('@formPerformance');
				cy.get('[data-section="audience-overview"]').should('have.class', 'is-ready');
				cy.get('#mailchimp-sf-ao-error-banner').should('have.attr', 'hidden');
			});

			it('Audience Overview does not fire its own AJAX action', () => {
				stubFormPerformance(() => wpJsonSuccessFp(buildFormPerformanceData()));
				cy.intercept('POST', '**/admin-ajax.php', (req) => {
					if (
						typeof req.body === 'string' &&
						req.body.includes('mailchimp_sf_get_audience_overview')
					) {
						throw new Error('Audience Overview should not fire its own AJAX call');
					}
				});
				cy.visit(analyticsUrl);
				cy.wait('@formPerformance');
				cy.get('[data-section="audience-overview"]').should('have.class', 'is-ready');
			});
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
			cy.get('#adminmenu li#toplevel_page_mailchimp_sf_options .wp-submenu').should(
				'not.contain',
				'Analytics',
			);
		});

		after(() => {
			cy.login();
			cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
			cy.mailchimpLoginIfNotAlreadyLoggedIn();
		});
	});
});
