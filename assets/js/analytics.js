/**
 * Mailchimp Analytics Page JavaScript
 *
 * @package Mailchimp
 */

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
	 * Format a date string (YYYY-MM-DD) to a display format (MM-DD-YYYY).
	 *
	 * @param {string} dateStr Date string in YYYY-MM-DD format.
	 * @returns {string} Formatted date string.
	 */
	function formatDisplayDate(dateStr) {
		const parts = dateStr.split('-');
		return `${parts[1]}-${parts[2]}-${parts[0]}`;
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

		const days = parseInt(appliedState.preset, 10);
		const to = new Date();
		const from = new Date();
		from.setDate(from.getDate() - days);
		return { from: toLocalDateString(from), to: toLocalDateString(to) };
	}

	/**
	 * Compute start/end dates for a preset and populate the date inputs.
	 */
	function syncDateInputs() {
		const range = getDateRange();
		if (range && dateFrom && dateTo) {
			dateFrom.value = range.from;
			dateTo.value = range.to;
		}
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
	 * Toggle the popover open/closed.
	 */
	function togglePopover() {
		if (!popover) {
			return;
		}
		const isOpen = popover.classList.contains('is-open');
		if (isOpen) {
			popover.classList.remove('is-open');
		} else {
			syncDateInputs();
			if (dateRangeSelect) {
				dateRangeSelect.value = appliedState.preset;
			}
			popover.classList.add('is-open');
		}
	}

	/**
	 * Close the popover without applying.
	 */
	function closePopover() {
		if (popover) {
			popover.classList.remove('is-open');
		}
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
		trigger.addEventListener('click', togglePopover);
	}

	if (cancelBtn) {
		cancelBtn.addEventListener('click', closePopover);
	}

	if (applyBtn) {
		applyBtn.addEventListener('click', applyDateRange);
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
