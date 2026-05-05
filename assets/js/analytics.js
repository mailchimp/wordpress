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
import '../css/analytics.css';

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
			format: DATEPICKER_FORMAT,
			autohide: true,
			maxDate: new Date(),
			weekStart: START_OF_WEEK,
		});

		toDatepicker = new Datepicker(dateTo, {
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

	// Initialize.
	updateTriggerLabel();
	syncDateInputs();
})();
