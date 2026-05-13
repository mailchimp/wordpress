/**
 * Mailchimp Analytics Page JavaScript
 *
 * @package Mailchimp
 */

/*
 * External dependencies
 */
import { Datepicker } from 'vanillajs-datepicker';
import 'vanillajs-datepicker/css/datepicker.css'; // eslint-disable-line import/no-unresolved
import '../css/analytics.scss';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

(function () {
	const dateRangeSelect = document.getElementById('mailchimp-sf-date-range');
	const dateFrom = document.getElementById('mailchimp-sf-date-from');
	const dateTo = document.getElementById('mailchimp-sf-date-to');
	const listFilter = document.getElementById('mailchimp-sf-list-filter');
	const trigger = document.getElementById('mailchimp-sf-date-picker-trigger');
	const triggerLabel = document.getElementById('mailchimp-sf-date-picker-label');
	const popover = document.getElementById('mailchimp-sf-date-picker-popover');
	const cancelBtn = document.getElementById('mailchimp-sf-date-picker-cancel');
	const applyBtn = document.getElementById('mailchimp-sf-date-picker-apply');
	const datePickerWrap = trigger ? trigger.closest('.mailchimp-sf-date-picker') : null;
	const settings = window.mailchimpSFAnalytics || {};
	const PHP_DATE_FORMAT = settings.dateFormat || 'Y-m-d';
	const START_OF_WEEK = Number.isFinite(settings.startOfWeek) ? settings.startOfWeek : 0;

	/**
	 * Translate a WordPress (PHP `date()`) format string
	 *
	 * @param {string} php PHP date format (e.g. `F j, Y` or `d/m/Y`).
	 * @returns {string} Equivalent vanillajs-datepicker format string.
	 */
	function phpToDatepickerFormat(php) {
		const map = {
			Y: 'yyyy',
			y: 'yyyy',
			F: 'MM',
			M: 'M',
			m: 'mm',
			n: 'm',
			d: 'dd',
			j: 'd',
			D: 'D',
			l: 'DD',
		};
		let out = '';
		for (let i = 0; i < php.length; i++) {
			const c = php.charAt(i);
			if (c === '\\' && i + 1 < php.length) {
				// PHP escape: emit the next character literally.
				out += php.charAt(i + 1);
				i += 1;
			} else {
				out += Object.prototype.hasOwnProperty.call(map, c) ? map[c] : c;
			}
		}
		return out;
	}

	const DATEPICKER_FORMAT = phpToDatepickerFormat(PHP_DATE_FORMAT);

	// Initialize datepicker only when both inputs are present.
	let fromDatepicker = null;
	let toDatepicker = null;

	if (dateFrom && dateTo) {
		fromDatepicker = new Datepicker(dateFrom, {
			maxView: 0,
			format: DATEPICKER_FORMAT,
			autohide: true,
			maxDate: new Date(),
			weekStart: START_OF_WEEK,
		});

		toDatepicker = new Datepicker(dateTo, {
			maxView: 0,
			format: DATEPICKER_FORMAT,
			autohide: true,
			maxDate: new Date(),
			weekStart: START_OF_WEEK,
		});
	}
	const PRESET_VALUES = ['7', '30', '90', '180', '365'];

	let appliedState = {
		preset: '30',
		from: '',
		to: '',
	};

	/**
	 * Format a Date object to a YYYY-MM-DD string in local time.
	 *
	 * @param {Date} date Date to format.
	 * @returns {string} Date string in YYYY-MM-DD format.
	 */
	function toLocalDateString(date) {
		const year = date.getFullYear();
		const month = String(date.getMonth() + 1).padStart(2, '0');
		const day = String(date.getDate()).padStart(2, '0');
		return `${year}-${month}-${day}`;
	}

	/**
	 * Parse an ISO `YYYY-MM-DD` string into a local-calendar Date object.
	 *
	 * @param {string} iso ISO date string.
	 * @returns {Date}
	 */
	function isoToLocalDate(iso) {
		const parts = iso.split('-').map(Number);
		return new Date(parts[0], parts[1] - 1, parts[2]);
	}

	/**
	 * Format a date string
	 *
	 * @param {string} dateStr Date string in YYYY-MM-DD format.
	 * @returns {string} Date formatted per the site's `date_format` option.
	 */
	function formatDisplayDate(dateStr) {
		const parts = dateStr.split('-').map(Number);
		const date = new Date(parts[0], parts[1] - 1, parts[2]);
		return Datepicker.formatDate(date, DATEPICKER_FORMAT);
	}

	/**
	 * Read a datepicker's selected date as ISO `YYYY-MM-DD`
	 *
	 * @param {Datepicker} datepicker vanillajs-datepicker instance.
	 * @returns {string} ISO date or empty string.
	 */
	function getDatepickerIso(datepicker) {
		if (!datepicker || typeof datepicker.getDate !== 'function') {
			return '';
		}
		const d = datepicker.getDate();
		return d instanceof Date ? toLocalDateString(d) : '';
	}

	/**
	 * Get the label text for the selected preset.
	 *
	 * @param {string} value The preset value.
	 * @returns {string} The label text.
	 */
	function getPresetLabel(value) {
		if (!dateRangeSelect) {
			return '';
		}
		const option = dateRangeSelect.querySelector(`option[value="${value}"]`);
		return option ? option.textContent.trim() : '';
	}

	/**
	 * Inclusive last-N-days range ending today (local calendar).
	 *
	 * @param {string} presetValue Numeric preset id (e.g. "7", "30").
	 * @returns {{ from: string, to: string }|null} Range or null if not a numeric preset.
	 */
	function getRangeForPreset(presetValue) {
		if (presetValue === 'custom') {
			return null;
		}
		const days = parseInt(presetValue, 10);
		if (Number.isNaN(days) || days < 1) {
			return null;
		}
		const to = new Date();
		const from = new Date();
		from.setDate(from.getDate() - (days - 1));
		return { from: toLocalDateString(from), to: toLocalDateString(to) };
	}

	/**
	 * Get the resolved date range based on current applied state.
	 *
	 * @returns {{ from: string, to: string }|null} Date range strings (YYYY-MM-DD) or null.
	 */
	function getDateRange() {
		if (appliedState.preset === 'custom') {
			if (!appliedState.from || !appliedState.to) {
				return null;
			}
			return { from: appliedState.from, to: appliedState.to };
		}

		return getRangeForPreset(appliedState.preset);
	}

	/**
	 * Populate start/end inputs from applied state (when opening popover).
	 */
	function syncDateInputs() {
		const range = getDateRange();
		if (range && fromDatepicker && toDatepicker) {
			fromDatepicker.setDate(isoToLocalDate(range.from));
			toDatepicker.setDate(isoToLocalDate(range.to));
		}
	}

	/**
	 * Fill date inputs from a preset (popover: user picked a non-custom range).
	 *
	 * @param {string} presetVal Preset value.
	 */
	function applyPresetToInputs(presetVal) {
		if (!fromDatepicker || !toDatepicker || presetVal === 'custom') {
			return;
		}
		const range = getRangeForPreset(presetVal);
		if (range) {
			fromDatepicker.setDate(isoToLocalDate(range.from));
			toDatepicker.setDate(isoToLocalDate(range.to));
		}
	}

	/**
	 * If current inputs match a rolling preset for today, select it; otherwise Custom.
	 */
	function syncSelectFromDateInputs() {
		if (!dateRangeSelect || !fromDatepicker || !toDatepicker) {
			return;
		}

		if (dateFrom.value) {
			fromDatepicker.setDate(dateFrom.value);
		}
		if (dateTo.value) {
			toDatepicker.setDate(dateTo.value);
		}

		const fromIso = getDatepickerIso(fromDatepicker);
		const toIso = getDatepickerIso(toDatepicker);
		if (!fromIso || !toIso) {
			return;
		}

		for (let i = 0; i < PRESET_VALUES.length; i++) {
			const preset = PRESET_VALUES[i];
			const range = getRangeForPreset(preset);
			if (range && range.from === fromIso && range.to === toIso) {
				dateRangeSelect.value = preset;
				return;
			}
		}
		dateRangeSelect.value = 'custom';
	}

	/**
	 * Update the trigger button label.
	 */
	function updateTriggerLabel() {
		if (!triggerLabel) {
			return;
		}
		if (appliedState.preset === 'custom') {
			if (appliedState.from && appliedState.to) {
				triggerLabel.textContent = `${formatDisplayDate(appliedState.from)} \u2013 ${formatDisplayDate(appliedState.to)}`;
			}
		} else {
			triggerLabel.textContent = getPresetLabel(appliedState.preset);
		}
	}

	/**
	 * Set the popover and date picker wrap open state.
	 *
	 * @param {boolean} open Whether the popover is visible.
	 */
	function setPopoverOpen(open) {
		if (popover) {
			popover.classList.toggle('is-open', open);
		}
		if (datePickerWrap) {
			datePickerWrap.classList.toggle('is-open', open);
		}
		if (trigger) {
			trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
		}
	}

	/**
	 * Toggle the popover open/closed.
	 */
	function togglePopover() {
		if (!popover) {
			return;
		}
		const isOpen = popover.classList.contains('is-open');
		if (isOpen) {
			setPopoverOpen(false);
		} else {
			syncDateInputs();
			if (dateRangeSelect) {
				dateRangeSelect.value = appliedState.preset;
			}
			setPopoverOpen(true);
		}
	}

	/**
	 * Close the popover without applying.
	 */
	function closePopover() {
		setPopoverOpen(false);
	}

	/**
	 * Apply the selected date range and close the popover.
	 */
	function applyDateRange() {
		if (!dateRangeSelect) {
			return;
		}

		const preset = dateRangeSelect.value;

		if (preset === 'custom') {
			const fromIso = getDatepickerIso(fromDatepicker);
			const toIso = getDatepickerIso(toDatepicker);
			if (!fromIso || !toIso) {
				return;
			}
			if (fromIso > toIso) {
				return;
			}
			appliedState = {
				preset: 'custom',
				from: fromIso,
				to: toIso,
			};
		} else {
			appliedState = {
				preset,
				from: '',
				to: '',
			};
		}

		updateTriggerLabel();
		closePopover();
		// eslint-disable-next-line no-use-before-define
		refreshAnalytics();
	}

	/**
	 * Refresh analytics sections when filters change.
	 */
	function refreshAnalytics() {
		const range = getDateRange();
		const listId = listFilter ? listFilter.value : '';

		if (!range) {
			return;
		}

		const event = new CustomEvent('mailchimp-analytics-refresh', {
			detail: {
				from: range.from,
				to: range.to,
				listId,
			},
		});
		document.dispatchEvent(event);
	}

	// Bind events.
	if (trigger) {
		trigger.setAttribute('aria-expanded', 'false');
		trigger.addEventListener('click', togglePopover);
	}

	if (cancelBtn) {
		cancelBtn.addEventListener('click', closePopover);
	}

	if (applyBtn) {
		applyBtn.addEventListener('click', applyDateRange);
	}

	if (dateRangeSelect) {
		dateRangeSelect.addEventListener('change', function () {
			if (dateRangeSelect.value === 'custom') {
				return;
			}
			applyPresetToInputs(dateRangeSelect.value);
		});
	}

	if (dateFrom) {
		dateFrom.addEventListener('change', syncSelectFromDateInputs);
		dateFrom.addEventListener('changeDate', syncSelectFromDateInputs);
	}
	if (dateTo) {
		dateTo.addEventListener('change', syncSelectFromDateInputs);
		dateTo.addEventListener('changeDate', syncSelectFromDateInputs);
	}

	if (listFilter) {
		listFilter.addEventListener('change', refreshAnalytics);
	}

	// Close popover when clicking outside.
	document.addEventListener('click', function (e) {
		if (
			popover &&
			popover.classList.contains('is-open') &&
			!popover.contains(e.target) &&
			trigger &&
			!trigger.contains(e.target)
		) {
			closePopover();
		}
	});

	/**
	 * Fetch analytics data via AJAX and update the content area.
	 *
	 * @param {object} detail Event detail with from, to, listId.
	 */
	function fetchAnalyticsData(detail) {
		if (!window.mailchimpSFAnalytics || !window.mailchimpSFAnalytics.ajax_url) {
			return;
		}

		const contentArea = document.getElementById('mailchimp-sf-analytics-content');
		if (!contentArea) {
			return;
		}

		const formData = new FormData();
		formData.append('action', 'mailchimp_sf_get_analytics');
		formData.append('nonce', window.mailchimpSFAnalytics.nonce);
		formData.append('list_id', detail.listId);
		formData.append('start_date', detail.from);
		formData.append('end_date', detail.to);

		fetch(window.mailchimpSFAnalytics.ajax_url, {
			method: 'POST',
			body: formData,
			credentials: 'same-origin',
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (response) {
				if (!response.success) {
					return;
				}

				const { data } = response;
				contentArea.innerHTML = JSON.stringify(data);
			})
			.catch(function () {});
	}

	// Listen for analytics refresh events.
	document.addEventListener('mailchimp-analytics-refresh', function (e) {
		fetchAnalyticsData(e.detail);
	});

	/**
	 * Subscriber change over time — diverging bar + totals donut.
	 * Loads independently from other analytics sections so an API error in
	 * this section does not affect KPIs or Form Performance.
	 */
	(function subscriberActivityModule() {
		const section = document.querySelector('[data-section="subscriber-activity"]');
		if (!section) {
			return;
		}

		const barCanvas = document.getElementById('mailchimp-sf-sa-bar');
		const donutCanvas = document.getElementById('mailchimp-sf-sa-donut');
		const netEl = document.getElementById('mailchimp-sf-sa-net');
		const totalNewEl = document.getElementById('mailchimp-sf-sa-total-new');
		const totalUnsubsEl = document.getElementById('mailchimp-sf-sa-total-unsubs');
		const dateRangeEl = document.getElementById('mailchimp-sf-sa-daterange');
		const noticeEl = document.getElementById('mailchimp-sf-sa-notice');
		const overlayEl = document.getElementById('mailchimp-sf-sa-overlay');
		const errorBannerEl = document.getElementById('mailchimp-sf-sa-error-banner');
		const errorMessageEl = document.getElementById('mailchimp-sf-sa-error-message');
		const retryBtnEl = document.getElementById('mailchimp-sf-sa-error-retry');

		const COLORS = {
			newFill: '#2b72fb',
			newBorder: '#2b72fb',
			unsubFill: '#fa4b42',
			unsubBorder: '#fa4b42',
			gridLine: 'rgba(0, 0, 0, 0.06)',
			zeroLine: 'rgba(0, 0, 0, 0.25)',
			text: '#6B7280',
		};

		const EM_DASH = '\u2014';

		const STRINGS = {
			loadingSubtitle: __('Loading subscriber activity…', 'mailchimp'),
			loadingOverlay: __('Loading subscriber activity…', 'mailchimp'),
			emptySubtitle: __('No data available for the selected date range', 'mailchimp'),
			emptyOverlay: __('No data available for this date range', 'mailchimp'),
			errorDefault: __(
				'Unable to load data for the selected date range. Please check your connection and try again.',
				'mailchimp',
			),
			limited: __(
				'Mailchimp subscriber activity is only available for the last 180 days. Showing available data.',
				'mailchimp',
			),
			newSubscribers: __('New Subscribers', 'mailchimp'),
			unsubscribes: __('Unsubscribes', 'mailchimp'),
		};

		const STATE_CLASSES = ['is-loading', 'is-ready', 'is-empty', 'is-error'];

		let barChart = null;
		let donutChart = null;
		let inFlight = null;
		let lastDetail = null;

		function setState(state) {
			STATE_CLASSES.forEach(function (cls) {
				section.classList.toggle(cls, cls === `is-${state}`);
			});
		}

		function setPlaceholderTotals() {
			if (netEl) {
				netEl.textContent = EM_DASH;
				netEl.classList.remove('is-positive', 'is-negative');
			}
			if (totalNewEl) {
				totalNewEl.textContent = EM_DASH;
			}
			if (totalUnsubsEl) {
				totalUnsubsEl.textContent = EM_DASH;
			}
		}

		function showNotice(message) {
			if (!noticeEl) {
				return;
			}
			if (message) {
				noticeEl.textContent = message;
				noticeEl.hidden = false;
			} else {
				noticeEl.textContent = '';
				noticeEl.hidden = true;
			}
		}

		function setOverlay(text) {
			if (overlayEl) {
				overlayEl.textContent = text || '';
			}
		}

		function setSubtitle(text) {
			if (dateRangeEl) {
				dateRangeEl.textContent = text || '';
			}
		}

		function destroyCharts() {
			if (barChart) {
				barChart.destroy();
				barChart = null;
			}
			if (donutChart) {
				donutChart.destroy();
				donutChart = null;
			}
		}

		function formatRangeLabel(from, to) {
			try {
				const fromDate = new Date(`${from}T00:00:00`);
				const toDate = new Date(`${to}T00:00:00`);
				const fmt = new Intl.DateTimeFormat(undefined, {
					month: 'short',
					day: 'numeric',
					year: 'numeric',
				});
				return `${fmt.format(fromDate)} – ${fmt.format(toDate)}`;
			} catch (err) {
				return `${from} – ${to}`;
			}
		}

		function setErrorBanner(visible, message) {
			if (!errorBannerEl) {
				return;
			}
			if (visible) {
				if (errorMessageEl) {
					errorMessageEl.textContent = message || STRINGS.errorDefault;
				}
				errorBannerEl.hidden = false;
			} else {
				errorBannerEl.hidden = true;
			}
		}

		function showLoading() {
			destroyCharts();
			showNotice('');
			setErrorBanner(false);
			setOverlay(STRINGS.loadingOverlay);
			setSubtitle(STRINGS.loadingSubtitle);
			setPlaceholderTotals();
			setState('loading');
		}

		function showEmpty() {
			destroyCharts();
			setErrorBanner(false);
			setOverlay(STRINGS.emptyOverlay);
			setSubtitle(STRINGS.emptySubtitle);
			setPlaceholderTotals();
			setState('empty');
		}

		function showError(message) {
			destroyCharts();
			showNotice('');
			setOverlay('');
			// Keep subtitle showing the last attempted date range if we have one.
			if (lastDetail && lastDetail.from && lastDetail.to) {
				setSubtitle(formatRangeLabel(lastDetail.from, lastDetail.to));
			}
			setPlaceholderTotals();
			setErrorBanner(true, message);
			setState('error');
		}

		function renderBar(data) {
			if (!barCanvas || typeof window.Chart === 'undefined') {
				return;
			}

			const labels = data.map(function (row) {
				return row.label;
			});
			const newSeries = data.map(function (row) {
				return row.new_subscribers || 0;
			});
			const unsubSeries = data.map(function (row) {
				return -Math.abs(row.unsubscribes || 0);
			});

			const config = {
				type: 'bar',
				data: {
					labels,
					datasets: [
						{
							label: STRINGS.unsubscribes,
							data: unsubSeries,
							backgroundColor: COLORS.unsubFill,
							borderColor: COLORS.unsubBorder,
							borderWidth: 0,
							borderRadius: 0,
							borderSkipped: false,
							maxBarThickness: 32,
						},
						{
							label: STRINGS.newSubscribers,
							data: newSeries,
							backgroundColor: COLORS.newFill,
							borderColor: COLORS.newBorder,
							borderWidth: 0,
							borderRadius: 0,
							borderSkipped: false,
							maxBarThickness: 32,
						},
					],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					interaction: { mode: 'index', intersect: false },
					plugins: {
						legend: {
							position: 'top',
							align: 'center',
							labels: {
								usePointStyle: true,
								pointStyle: 'rectRounded',
								boxWidth: 10,
								boxHeight: 10,
								padding: 16,
								color: COLORS.text,
							},
						},
						tooltip: {
							callbacks: {
								label(ctx) {
									const value = Math.abs(ctx.parsed.y || 0);
									return `${ctx.dataset.label}: ${value}`;
								},
							},
						},
					},
					scales: {
						x: {
							grid: {
								color: COLORS.gridLine,
								drawBorder: false,
								drawOnChartArea: true,
								drawTicks: false,
							},
							ticks: { color: COLORS.text },
						},
						y: {
							beginAtZero: true,
							grid: {
								color(ctx) {
									return ctx.tick && ctx.tick.value === 0
										? COLORS.zeroLine
										: COLORS.gridLine;
								},
								drawBorder: false,
								drawOnChartArea: true,
							},
							ticks: {
								color: COLORS.text,
								callback(value) {
									return value;
								},
							},
						},
					},
				},
			};

			barChart = new window.Chart(barCanvas.getContext('2d'), config);
		}

		function renderDonut(totalNew, totalUnsubs) {
			if (!donutCanvas || typeof window.Chart === 'undefined') {
				return;
			}
			const total = (totalNew || 0) + (totalUnsubs || 0);
			const data = total > 0 ? [totalNew || 0, totalUnsubs || 0] : [1, 0];
			const colors =
				total > 0
					? [COLORS.newBorder, COLORS.unsubBorder]
					: ['rgba(0, 0, 0, 0.08)', 'rgba(0, 0, 0, 0.08)'];

			donutChart = new window.Chart(donutCanvas.getContext('2d'), {
				type: 'doughnut',
				data: {
					labels: [STRINGS.newSubscribers, STRINGS.unsubscribes],
					datasets: [
						{
							data,
							backgroundColor: colors,
							borderWidth: 0,
							cutout: '78%',
						},
					],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: { display: false },
						tooltip: { enabled: total > 0 },
					},
				},
			});
		}

		function renderTotals(payload) {
			const net = payload.net_change || 0;
			if (netEl) {
				const sign = net > 0 ? '+' : '';
				netEl.textContent = `${sign}${net}`;
				netEl.classList.toggle('is-positive', net > 0);
				netEl.classList.toggle('is-negative', net < 0);
			}
			if (totalNewEl) {
				totalNewEl.textContent = String(payload.total_new || 0);
			}
			if (totalUnsubsEl) {
				totalUnsubsEl.textContent = String(payload.total_unsubs || 0);
			}
		}

		function render(payload, fromLabel, toLabel) {
			destroyCharts();
			setErrorBanner(false);

			if (!Array.isArray(payload.data) || payload.data.length === 0) {
				showEmpty();
				return;
			}

			showNotice(payload.limited ? STRINGS.limited : '');
			setSubtitle(formatRangeLabel(fromLabel, toLabel));
			setOverlay('');
			setState('ready');
			renderBar(payload.data);
			renderDonut(payload.total_new, payload.total_unsubs);
			renderTotals(payload);
		}

		function fetchActivity(detail) {
			if (!window.mailchimpSFAnalytics || !window.mailchimpSFAnalytics.ajax_url) {
				showError();
				return;
			}
			if (!detail || !detail.listId || !detail.from || !detail.to) {
				showEmpty();
				return;
			}

			lastDetail = {
				listId: detail.listId,
				from: detail.from,
				to: detail.to,
			};

			if (inFlight && typeof inFlight.abort === 'function') {
				inFlight.abort();
			}

			const controller =
				typeof window.AbortController !== 'undefined' ? new AbortController() : null;
			inFlight = controller;

			const formData = new FormData();
			formData.append('action', 'mailchimp_sf_get_subscriber_activity');
			formData.append('nonce', window.mailchimpSFAnalytics.nonce);
			formData.append('list_id', detail.listId);
			formData.append('date_from', detail.from);
			formData.append('date_to', detail.to);

			showLoading();

			fetch(window.mailchimpSFAnalytics.ajax_url, {
				method: 'POST',
				body: formData,
				credentials: 'same-origin',
				signal: controller ? controller.signal : undefined,
			})
				.then(function (response) {
					return response.json().catch(function () {
						return null;
					});
				})
				.then(function (body) {
					inFlight = null;
					if (!body || body.success !== true || !body.data) {
						const message =
							body && body.data && body.data.message ? body.data.message : '';
						showError(message);
						return;
					}
					render(body.data, detail.from, detail.to);
				})
				.catch(function (err) {
					if (err && err.name === 'AbortError') {
						return;
					}
					inFlight = null;
					showError();
				});
		}

		if (retryBtnEl) {
			retryBtnEl.addEventListener('click', function () {
				if (lastDetail) {
					fetchActivity(lastDetail);
				}
			});
		}

		document.addEventListener('mailchimp-analytics-refresh', function (e) {
			fetchActivity(e.detail);
		});
	})();

	// Initialize.
	updateTriggerLabel();
	syncDateInputs();
	refreshAnalytics();
})();
