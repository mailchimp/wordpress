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

	// Initialize datepicker only when both inputs are present.
	let fromDatepicker = null;
	let toDatepicker = null;

	if (dateFrom && dateTo) {
		fromDatepicker = new Datepicker(dateFrom, {
			format: 'yyyy-mm-dd',
			autohide: true,
			maxDate: new Date(),
		});

		toDatepicker = new Datepicker(dateTo, {
			format: 'yyyy-mm-dd',
			autohide: true,
			maxDate: new Date(),
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
	 * Format a date string (YYYY-MM-DD) for the trigger label (locale-aware).
	 * Parses as local calendar date — avoids UTC midnight shifts from `new Date("YYYY-MM-DD")`.
	 *
	 * @param {string} dateStr Date string in YYYY-MM-DD format.
	 * @returns {string} Formatted date string.
	 */
	function formatDisplayDate(dateStr) {
		const parts = dateStr.split('-').map(Number);
		const date = new Date(parts[0], parts[1] - 1, parts[2]);
		return date.toLocaleDateString(undefined, {
			month: 'short',
			day: 'numeric',
			year: 'numeric',
		});
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
		if (range && dateFrom && dateTo) {
			dateFrom.value = range.from;
			dateTo.value = range.to;
			fromDatepicker.setDate(range.from);
			toDatepicker.setDate(range.to);
		}
	}

	/**
	 * Fill date inputs from a preset (popover: user picked a non-custom range).
	 *
	 * @param {string} presetVal Preset value.
	 */
	function applyPresetToInputs(presetVal) {
		if (!dateFrom || !dateTo || presetVal === 'custom') {
			return;
		}
		const range = getRangeForPreset(presetVal);
		if (range) {
			dateFrom.value = range.from;
			dateTo.value = range.to;
			fromDatepicker.setDate(range.from);
			toDatepicker.setDate(range.to);
		}
	}

	/**
	 * If current inputs match a rolling preset for today, select it; otherwise Custom.
	 */
	function syncSelectFromDateInputs() {
		if (!dateRangeSelect || !dateFrom || !dateTo) {
			return;
		}
		if (!dateFrom.value || !dateTo.value) {
			return;
		}

		fromDatepicker.setDate(dateFrom.value);
		toDatepicker.setDate(dateTo.value);

		for (let i = 0; i < PRESET_VALUES.length; i++) {
			const preset = PRESET_VALUES[i];
			const range = getRangeForPreset(preset);
			if (range && range.from === dateFrom.value && range.to === dateTo.value) {
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
			if (!dateFrom.value || !dateTo.value) {
				return;
			}
			const from = new Date(`${dateFrom.value}T00:00:00`);
			const to = new Date(`${dateTo.value}T23:59:59`);
			if (from > to) {
				return;
			}
			appliedState = {
				preset: 'custom',
				from: dateFrom.value,
				to: dateTo.value,
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
