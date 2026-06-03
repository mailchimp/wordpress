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
	/**
	 * `true` when the user has set the OS-level "Reduce motion" preference.
	 * Used to disable Chart.js animations
	 */
	const PREFERS_REDUCED_MOTION =
		typeof window.matchMedia === 'function' &&
		window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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

	// Close popover on Escape and return focus to the trigger button
	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') {
			return;
		}
		if (!popover || !popover.classList.contains('is-open')) {
			return;
		}

		const openCalendar = document.querySelector('.datepicker.active');
		if (openCalendar) {
			return;
		}
		closePopover();
		if (trigger) {
			trigger.focus();
		}
	});

	/**
	 * Forms performance over time
	 */
	(function formPerformanceModule() {
		const section = document.querySelector('[data-section="form-performance"]');
		if (!section) {
			return;
		}

		const chartCanvas = document.getElementById('mailchimp-sf-fp-line');
		const dateRangeEl = document.getElementById('mailchimp-sf-fp-daterange');
		const overlayEl = document.getElementById('mailchimp-sf-fp-overlay');
		const errorBannerEl = document.getElementById('mailchimp-sf-fp-error-banner');
		const errorMessageEl = document.getElementById('mailchimp-sf-fp-error-message');
		const retryBtnEl = document.getElementById('mailchimp-sf-fp-error-retry');
		const dataTableEl = document.getElementById('mailchimp-sf-fp-data-table');

		const COLORS = {
			viewsFill: '#3B82F6',
			viewsBorder: '#2563EB',
			submissionsFill: '#0E9384',
			submissionsBorder: '#0B7A6E',
			rateBorder: '#A88008',
			gridLine: 'rgba(0, 0, 0, 0.06)',
			text: '#6B7280',
			// Legend chip fills — translucent version of each bar color so the
			// legend markers match the outlined-chip style from the Figma spec.
			viewsLegendFill: 'rgba(59, 130, 246, 0.35)',
			submissionsLegendFill: 'rgba(14, 147, 132, 0.35)',
		};

		const STRINGS = {
			loadingSubtitle: __('Loading form performance…', 'mailchimp'),
			loadingOverlay: __('Loading form performance…', 'mailchimp'),
			emptySubtitle: __('No submissions recorded for the selected date range', 'mailchimp'),
			emptyOverlay: __('No data available for this date range', 'mailchimp'),
			errorDefault: __(
				'Unable to load data for the selected date range. Please check your connection and try again.',
				'mailchimp',
			),
			reconnect: __('Reconnect Mailchimp Account', 'mailchimp'),
			views: __('Form Views', 'mailchimp'),
			submissions: __('Submissions', 'mailchimp'),
			conversionRate: __('Conversion Rate', 'mailchimp'),
		};

		let lastErrorCode = '';

		const STATE_CLASSES = ['is-loading', 'is-ready', 'is-empty', 'is-error'];

		let chart = null;
		let inFlight = null;
		let lastDetail = null;

		function setState(state) {
			STATE_CLASSES.forEach(function (cls) {
				section.classList.toggle(cls, cls === `is-${state}`);
			});
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

		/**
		 * Build the visually-hidden screen-reader data table for the chart.
		 *
		 * @param {Array}  rows      Bucket rows from the payload.
		 * @param {string} fromLabel Range start (Y-m-d).
		 * @param {string} toLabel   Range end (Y-m-d).
		 */
		function renderDataTable(rows, fromLabel, toLabel) {
			if (!dataTableEl) {
				return;
			}

			const captionText = __(
				'List performance over time: views, submissions, and conversion rate per bucket for %1$s to %2$s.',
				'mailchimp',
			)
				.replace('%1$s', fromLabel)
				.replace('%2$s', toLabel);

			const headerCells = [
				__('Period', 'mailchimp'),
				__('Form Views', 'mailchimp'),
				__('Submissions', 'mailchimp'),
				__('Conversion Rate', 'mailchimp'),
			];

			const table = document.createElement('table');

			const caption = document.createElement('caption');
			caption.textContent = captionText;
			table.appendChild(caption);

			const thead = document.createElement('thead');
			const headRow = document.createElement('tr');
			headerCells.forEach(function (text) {
				const th = document.createElement('th');
				th.scope = 'col';
				th.textContent = text;
				headRow.appendChild(th);
			});
			thead.appendChild(headRow);
			table.appendChild(thead);

			const tbody = document.createElement('tbody');
			rows.forEach(function (row) {
				const tr = document.createElement('tr');

				const rowHeader = document.createElement('th');
				rowHeader.scope = 'row';
				rowHeader.textContent = row.label || '';
				tr.appendChild(rowHeader);

				[
					String(row.views || 0),
					String(row.submissions || 0),
					`${Number(row.conversion_rate || 0).toFixed(2)}%`,
				].forEach(function (text) {
					const td = document.createElement('td');
					td.textContent = text;
					tr.appendChild(td);
				});

				tbody.appendChild(tr);
			});
			table.appendChild(tbody);

			dataTableEl.innerHTML = '';
			dataTableEl.appendChild(table);
		}

		/**
		 * Empty the screen-reader data table
		 */
		function clearDataTable() {
			if (dataTableEl) {
				dataTableEl.innerHTML = '';
			}
		}

		function destroyCharts() {
			if (chart) {
				chart.destroy();
				chart = null;
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
				return `\u2066${fmt.format(fromDate)} – ${fmt.format(toDate)}\u2069`;
			} catch (err) {
				return `\u2066${from} – ${to}\u2069`;
			}
		}

		function setErrorBanner(visible, message, errorCode) {
			if (!errorBannerEl) {
				return;
			}
			lastErrorCode = visible ? errorCode || '' : '';
			if (visible) {
				if (errorMessageEl) {
					errorMessageEl.textContent = message || STRINGS.errorDefault;
				}
				if (retryBtnEl) {
					const settingsUrl =
						(window.mailchimpSFAnalytics && window.mailchimpSFAnalytics.settingsUrl) ||
						'';
					if (lastErrorCode === 'not_connected' && settingsUrl) {
						retryBtnEl.textContent = STRINGS.reconnect;
						retryBtnEl.hidden = false;
					} else {
						retryBtnEl.hidden = true;
					}
				}
				errorBannerEl.hidden = false;
			} else {
				if (retryBtnEl) {
					retryBtnEl.hidden = true;
				}
				errorBannerEl.hidden = true;
			}
		}

		function showLoading() {
			destroyCharts();
			clearDataTable();
			setErrorBanner(false);
			setOverlay(STRINGS.loadingOverlay);
			setSubtitle(STRINGS.loadingSubtitle);
			setState('loading');
		}

		function showEmpty() {
			destroyCharts();
			clearDataTable();
			setErrorBanner(false);
			setOverlay(STRINGS.emptyOverlay);
			setSubtitle(STRINGS.emptySubtitle);
			setState('empty');
		}

		function showError(message, errorCode) {
			destroyCharts();
			clearDataTable();
			setOverlay('');
			if (lastDetail && lastDetail.from && lastDetail.to) {
				setSubtitle(formatRangeLabel(lastDetail.from, lastDetail.to));
			}
			setErrorBanner(true, message, errorCode);
			setState('error');
		}

		/**
		 * Render the bar+line chart from API rows.
		 *
		 * @param {Array} rows Payload `data` rows from the API.
		 */
		function renderChart(rows) {
			if (!chartCanvas || typeof window.Chart === 'undefined') {
				return;
			}

			const labels = rows.map(function (r) {
				return r.label;
			});
			const views = rows.map(function (r) {
				return r.views || 0;
			});
			const submissions = rows.map(function (r) {
				return r.submissions || 0;
			});
			const rate = rows.map(function (r) {
				return r.conversion_rate || 0;
			});

			chart = new window.Chart(chartCanvas.getContext('2d'), {
				type: 'bar',
				data: {
					labels,
					datasets: [
						{
							type: 'bar',
							label: STRINGS.views,
							data: views,
							backgroundColor: COLORS.viewsFill,
							borderColor: COLORS.viewsBorder,
							borderWidth: 0,
							borderRadius: 0,
							maxBarThickness: 22,
							order: 2,
							yAxisID: 'y',
						},
						{
							type: 'bar',
							label: STRINGS.submissions,
							data: submissions,
							backgroundColor: COLORS.submissionsFill,
							borderColor: COLORS.submissionsBorder,
							borderWidth: 0,
							borderRadius: 0,
							maxBarThickness: 22,
							order: 2,
							yAxisID: 'y',
						},
						{
							type: 'line',
							label: STRINGS.conversionRate,
							data: rate,
							borderColor: COLORS.rateBorder,
							backgroundColor: COLORS.rateBorder,
							borderWidth: 2,
							pointBackgroundColor: COLORS.rateBorder,
							pointBorderColor: COLORS.rateBorder,
							pointRadius: 3,
							pointHoverRadius: 5,
							tension: 0.1,
							fill: false,
							order: 1,
							yAxisID: 'y1',
						},
					],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					animation: PREFERS_REDUCED_MOTION ? false : undefined,
					interaction: { mode: 'index', intersect: false },
					plugins: {
						legend: {
							position: 'top',
							align: 'center',
							labels: {
								usePointStyle: true,
								pointStyleWidth: 36,
								boxHeight: 20,
								padding: 24,
								color: COLORS.text,
								generateLabels(ci) {
									const legendFills = [
										COLORS.viewsLegendFill,
										COLORS.submissionsLegendFill,
									];
									return ci.data.datasets.map(function (dataset, i) {
										const isLine = dataset.type === 'line';
										return {
											text: dataset.label,
											fillStyle: isLine
												? 'transparent'
												: legendFills[i] || dataset.backgroundColor,
											strokeStyle: isLine
												? dataset.borderColor
												: dataset.backgroundColor,
											lineWidth: 2,
											pointStyle: isLine ? 'line' : 'rect',
											hidden: !ci.isDatasetVisible(i),
											datasetIndex: i,
										};
									});
								},
							},
						},
						tooltip: {
							callbacks: {
								label(ctx) {
									const value = ctx.parsed.y || 0;
									if (ctx.dataset.yAxisID === 'y1') {
										return `${ctx.dataset.label}: ${value.toFixed(1)}%`;
									}
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
								drawTicks: false,
							},
							ticks: { color: COLORS.text },
						},
						y: {
							type: 'linear',
							position: 'left',
							beginAtZero: true,
							grid: { color: COLORS.gridLine, drawBorder: false },
							ticks: { color: COLORS.text, precision: 0 },
						},
						y1: {
							type: 'linear',
							position: 'right',
							beginAtZero: true,
							max: 110,
							grid: { drawOnChartArea: false },
							ticks: {
								color: COLORS.text,
								stepSize: 10,
								callback(value) {
									return value > 100 ? '' : `${value}%`;
								},
							},
						},
					},
				},
			});
		}

		function render(payload, fromLabel, toLabel) {
			destroyCharts();
			setErrorBanner(false);

			const rows = Array.isArray(payload.data) ? payload.data : [];
			const totalViews = payload.total_views || 0;
			const totalSubs = payload.total_submissions || 0;

			// Empty when there's literally no tracked activity for the range.
			if (rows.length === 0 || (totalViews === 0 && totalSubs === 0)) {
				showEmpty();
				return;
			}

			setSubtitle(formatRangeLabel(fromLabel, toLabel));
			setOverlay('');
			setState('ready');
			renderChart(rows);
			renderDataTable(rows, fromLabel, toLabel);
		}

		/**
		 * Custom event so other analytics modules (Audience
		 * Overview, etc.) can render from the same fetch without making
		 * their own AJAX call.
		 *
		 * @param {string} name Event suffix — appended to `mailchimp-analytics-`.
		 * @param {object} eventDetail Payload passed as the event's `detail`.
		 */
		function broadcast(name, eventDetail) {
			document.dispatchEvent(
				new CustomEvent(`mailchimp-analytics-${name}`, { detail: eventDetail }),
			);
		}

		function fetchPerformance(detail) {
			if (!window.mailchimpSFAnalytics || !window.mailchimpSFAnalytics.ajax_url) {
				showError();
				broadcast('error', { message: STRINGS.errorDefault });
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
			formData.append('action', 'mailchimp_sf_get_form_performance');
			formData.append('nonce', window.mailchimpSFAnalytics.nonce);
			formData.append('list_id', detail.listId);
			formData.append('date_from', detail.from);
			formData.append('date_to', detail.to);

			showLoading();
			broadcast('loading', { from: detail.from, to: detail.to });

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
						const errorCode =
							body && body.data && body.data.error_code ? body.data.error_code : '';
						showError(message, errorCode);
						broadcast('error', {
							message: message || STRINGS.errorDefault,
							errorCode,
						});
						return;
					}
					render(body.data, detail.from, detail.to);
					broadcast('loaded', {
						data: body.data,
						from: detail.from,
						to: detail.to,
					});
				})
				.catch(function (err) {
					if (err && err.name === 'AbortError') {
						return;
					}
					inFlight = null;
					showError();
					broadcast('error', { message: STRINGS.errorDefault });
				});
		}

		if (retryBtnEl) {
			retryBtnEl.addEventListener('click', function () {
				if (lastErrorCode === 'not_connected') {
					const settingsUrl =
						(window.mailchimpSFAnalytics && window.mailchimpSFAnalytics.settingsUrl) ||
						'';
					if (settingsUrl) {
						window.location.href = settingsUrl;
					}
					return;
				}
				if (lastDetail) {
					fetchPerformance(lastDetail);
				}
			});
		}

		document.addEventListener('mailchimp-analytics-refresh', function (e) {
			fetchPerformance(e.detail);
		});
	})();

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
		const dataTableEl = document.getElementById('mailchimp-sf-sa-data-table');

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
			reconnect: __('Reconnect Mailchimp Account', 'mailchimp'),
			newSubscribers: __('New Subscribers', 'mailchimp'),
			unsubscribes: __('Unsubscribes', 'mailchimp'),
		};

		const STATE_CLASSES = ['is-loading', 'is-ready', 'is-empty', 'is-error'];

		let barChart = null;
		let donutChart = null;
		let inFlight = null;
		let lastDetail = null;
		let lastErrorCode = '';

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

		/**
		 * Build the visually-hidden screen-reader data table for the
		 * subscriber activity chart
		 *
		 * @param {Array}  rows      Bucket rows from the payload.
		 * @param {string} fromLabel Range start (Y-m-d).
		 * @param {string} toLabel   Range end (Y-m-d).
		 */
		function renderDataTable(rows, fromLabel, toLabel) {
			if (!dataTableEl) {
				return;
			}

			const captionText = __(
				'Subscriber change over time: new subscribers and unsubscribes per bucket for %1$s to %2$s.',
				'mailchimp',
			)
				.replace('%1$s', fromLabel)
				.replace('%2$s', toLabel);

			const headerCells = [
				__('Period', 'mailchimp'),
				__('New Subscribers', 'mailchimp'),
				__('Unsubscribes', 'mailchimp'),
			];

			const table = document.createElement('table');

			const caption = document.createElement('caption');
			caption.textContent = captionText;
			table.appendChild(caption);

			const thead = document.createElement('thead');
			const headRow = document.createElement('tr');
			headerCells.forEach(function (text) {
				const th = document.createElement('th');
				th.scope = 'col';
				th.textContent = text;
				headRow.appendChild(th);
			});
			thead.appendChild(headRow);
			table.appendChild(thead);

			const tbody = document.createElement('tbody');
			rows.forEach(function (row) {
				const tr = document.createElement('tr');

				const rowHeader = document.createElement('th');
				rowHeader.scope = 'row';
				rowHeader.textContent = row.label || '';
				tr.appendChild(rowHeader);

				[String(row.new_subscribers || 0), String(row.unsubscribes || 0)].forEach(
					function (text) {
						const td = document.createElement('td');
						td.textContent = text;
						tr.appendChild(td);
					},
				);

				tbody.appendChild(tr);
			});
			table.appendChild(tbody);

			dataTableEl.innerHTML = '';
			dataTableEl.appendChild(table);
		}

		/**
		 * Empty the screen-reader data table
		 */
		function clearDataTable() {
			if (dataTableEl) {
				dataTableEl.innerHTML = '';
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
				return `\u2066${fmt.format(fromDate)} – ${fmt.format(toDate)}\u2069`;
			} catch (err) {
				return `\u2066${from} – ${to}\u2069`;
			}
		}

		function setErrorBanner(visible, message, errorCode) {
			if (!errorBannerEl) {
				return;
			}
			lastErrorCode = visible ? errorCode || '' : '';
			if (visible) {
				if (errorMessageEl) {
					errorMessageEl.textContent = message || STRINGS.errorDefault;
				}
				if (retryBtnEl) {
					const settingsUrl =
						(window.mailchimpSFAnalytics && window.mailchimpSFAnalytics.settingsUrl) ||
						'';
					if (lastErrorCode === 'not_connected' && settingsUrl) {
						retryBtnEl.textContent = STRINGS.reconnect;
						retryBtnEl.hidden = false;
					} else {
						retryBtnEl.hidden = true;
					}
				}
				errorBannerEl.hidden = false;
			} else {
				if (retryBtnEl) {
					retryBtnEl.hidden = true;
				}
				errorBannerEl.hidden = true;
			}
		}

		function showLoading() {
			destroyCharts();
			clearDataTable();
			showNotice('');
			setErrorBanner(false);
			setOverlay(STRINGS.loadingOverlay);
			setSubtitle(STRINGS.loadingSubtitle);
			setPlaceholderTotals();
			setState('loading');
		}

		function showEmpty() {
			destroyCharts();
			clearDataTable();
			setErrorBanner(false);
			setOverlay(STRINGS.emptyOverlay);
			setSubtitle(STRINGS.emptySubtitle);
			setPlaceholderTotals();
			setState('empty');
		}

		function showError(message, errorCode) {
			destroyCharts();
			clearDataTable();
			showNotice('');
			setOverlay('');
			// Keep subtitle showing the last attempted date range if we have one.
			if (lastDetail && lastDetail.from && lastDetail.to) {
				setSubtitle(formatRangeLabel(lastDetail.from, lastDetail.to));
			}
			setPlaceholderTotals();
			setErrorBanner(true, message, errorCode);
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
					animation: PREFERS_REDUCED_MOTION ? false : undefined,
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
					animation: PREFERS_REDUCED_MOTION ? false : undefined,
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

			const rows = Array.isArray(payload.data) ? payload.data : [];
			const totalNew = payload.total_new || 0;
			const totalUnsubs = payload.total_unsubs || 0;

			// Match the Form Performance card's empty-state behavior
			if (rows.length === 0 || (totalNew === 0 && totalUnsubs === 0)) {
				showEmpty();
				return;
			}

			showNotice(payload.limited ? STRINGS.limited : '');
			setSubtitle(formatRangeLabel(fromLabel, toLabel));
			setOverlay('');
			setState('ready');
			renderBar(rows);
			renderDonut(totalNew, totalUnsubs);
			renderTotals(payload);
			renderDataTable(rows, fromLabel, toLabel);
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
						const errorCode =
							body && body.data && body.data.error_code ? body.data.error_code : '';
						showError(message, errorCode);
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
				if (lastErrorCode === 'not_connected') {
					const settingsUrl =
						(window.mailchimpSFAnalytics && window.mailchimpSFAnalytics.settingsUrl) ||
						'';
					if (settingsUrl) {
						window.location.href = settingsUrl;
					}
					return;
				}
				if (lastDetail) {
					fetchActivity(lastDetail);
				}
			});
		}

		document.addEventListener('mailchimp-analytics-refresh', function (e) {
			fetchActivity(e.detail);
		});
	})();

	/**
	 * Audience Overview KPI block — Total subscribers, Form views, New submissions, Conversion rate.
	 */
	(function audienceOverviewModule() {
		const section = document.querySelector('[data-section="audience-overview"]');
		if (!section) {
			return;
		}

		const subscribersEl = document.getElementById('mailchimp-sf-ao-total-subscribers');
		const viewsEl = document.getElementById('mailchimp-sf-ao-views');
		const submissionsEl = document.getElementById('mailchimp-sf-ao-submissions');
		const rateEl = document.getElementById('mailchimp-sf-ao-rate');
		const dateRangeEl = document.getElementById('mailchimp-sf-ao-daterange');
		const errorBannerEl = document.getElementById('mailchimp-sf-ao-error-banner');
		const errorMessageEl = document.getElementById('mailchimp-sf-ao-error-message');
		const retryBtnEl = document.getElementById('mailchimp-sf-ao-error-retry');

		const STRINGS = {
			loadingSubtitle: __('Loading audience overview…', 'mailchimp'),
			errorDefault: __(
				'Unable to load audience overview. Please check your connection and try again.',
				'mailchimp',
			),
			reconnect: __('Reconnect Mailchimp Account', 'mailchimp'),
		};

		const STATE_CLASSES = ['is-loading', 'is-ready', 'is-error'];

		let lastDetail = null;
		let lastErrorCode = '';

		function setState(state) {
			STATE_CLASSES.forEach(function (cls) {
				section.classList.toggle(cls, cls === `is-${state}`);
			});
		}

		function setSubtitle(text) {
			if (dateRangeEl) {
				dateRangeEl.textContent = text || '';
			}
		}

		function setErrorBanner(visible, message, errorCode) {
			if (!errorBannerEl) {
				return;
			}
			lastErrorCode = visible ? errorCode || '' : '';
			if (visible) {
				if (errorMessageEl) {
					errorMessageEl.textContent = message || STRINGS.errorDefault;
				}
				if (retryBtnEl) {
					const settingsUrl =
						(window.mailchimpSFAnalytics && window.mailchimpSFAnalytics.settingsUrl) ||
						'';
					if (lastErrorCode === 'not_connected' && settingsUrl) {
						retryBtnEl.textContent = STRINGS.reconnect;
						retryBtnEl.hidden = false;
					} else {
						retryBtnEl.hidden = true;
					}
				}
				errorBannerEl.hidden = false;
			} else {
				if (retryBtnEl) {
					retryBtnEl.hidden = true;
				}
				errorBannerEl.hidden = true;
			}
		}

		function setPlaceholders() {
			[subscribersEl, viewsEl, submissionsEl, rateEl].forEach(function (el) {
				if (el) {
					el.textContent = '-';
				}
			});
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
				return `\u2066${fmt.format(fromDate)} – ${fmt.format(toDate)}\u2069`;
			} catch (err) {
				return `\u2066${from} – ${to}\u2069`;
			}
		}

		function formatNumber(n) {
			if (n === null || typeof n === 'undefined') {
				return '-';
			}
			try {
				return new Intl.NumberFormat().format(n);
			} catch (err) {
				return String(n);
			}
		}

		function showLoading() {
			setErrorBanner(false);
			setSubtitle(STRINGS.loadingSubtitle);
			setPlaceholders();
			setState('loading');
		}

		function showError(message, errorCode) {
			if (lastDetail && lastDetail.from && lastDetail.to) {
				setSubtitle(formatRangeLabel(lastDetail.from, lastDetail.to));
			}
			setPlaceholders();
			setErrorBanner(true, message, errorCode);
			setState('error');
		}

		function render(data, fromLabel, toLabel) {
			setErrorBanner(false);
			setSubtitle(formatRangeLabel(fromLabel, toLabel));

			if (subscribersEl) {
				subscribersEl.textContent = formatNumber(data.total_subscribers);
			}
			if (viewsEl) {
				viewsEl.textContent = formatNumber(data.total_views);
			}
			if (submissionsEl) {
				submissionsEl.textContent = formatNumber(data.total_submissions);
			}
			if (rateEl) {
				const rate = data.total_conversion_rate;
				rateEl.textContent =
					rate === null || typeof rate === 'undefined'
						? '-'
						: `${Number(rate).toFixed(2)}%`;
			}

			setState('ready');
		}

		document.addEventListener('mailchimp-analytics-refresh', function (e) {
			if (e.detail) {
				lastDetail = {
					listId: e.detail.listId,
					from: e.detail.from,
					to: e.detail.to,
				};
			}
		});

		document.addEventListener('mailchimp-analytics-loading', function () {
			showLoading();
		});

		document.addEventListener('mailchimp-analytics-loaded', function (e) {
			if (e.detail && e.detail.data) {
				render(e.detail.data, e.detail.from, e.detail.to);
			}
		});

		document.addEventListener('mailchimp-analytics-error', function (e) {
			showError(e.detail && e.detail.message, e.detail && e.detail.errorCode);
		});

		if (retryBtnEl) {
			retryBtnEl.addEventListener('click', function () {
				if (lastErrorCode === 'not_connected') {
					const settingsUrl =
						(window.mailchimpSFAnalytics && window.mailchimpSFAnalytics.settingsUrl) ||
						'';
					if (settingsUrl) {
						window.location.href = settingsUrl;
					}
					return;
				}
				if (!lastDetail) {
					return;
				}

				document.dispatchEvent(
					new CustomEvent('mailchimp-analytics-refresh', { detail: lastDetail }),
				);
			});
		}
	})();

	// Initialize.
	updateTriggerLabel();
	syncDateInputs();
	refreshAnalytics();
})();
