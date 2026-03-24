/**
 * Mailchimp Analytics Page JavaScript
 *
 * @package Mailchimp
 */

(function () {
	const dateRangeSelect = document.getElementById('mailchimp-sf-date-range');
	const customDates = document.querySelector('.mailchimp-sf-custom-dates');
	const dateFrom = document.getElementById('mailchimp-sf-date-from');
	const dateTo = document.getElementById('mailchimp-sf-date-to');
	const listFilter = document.getElementById('mailchimp-sf-list-filter');
	const resolvedDisplay = document.getElementById('mailchimp-sf-resolved-date-range');

	/**
	 * Format a Date object to a human-readable string.
	 *
	 * @param {Date} date Date to format.
	 * @returns {string} Formatted date string.
	 */
	function formatDate(date) {
		return date.toLocaleDateString(undefined, {
			year: 'numeric',
			month: 'short',
			day: 'numeric',
		});
	}

	/**
	 * Get the resolved date range based on current filter selection.
	 *
	 * @returns {{ from: Date, to: Date }|null} Date range or null.
	 */
	function getDateRange() {
		const { value } = dateRangeSelect;
		let to = new Date();
		let from;

		if (value === 'custom') {
			if (!dateFrom.value || !dateTo.value) {
				return null;
			}
			from = new Date(`${dateFrom.value}T00:00:00`);
			to = new Date(`${dateTo.value}T23:59:59`);
			return { from, to };
		}

		const days = parseInt(value, 10);
		from = new Date();
		from.setDate(from.getDate() - days);
		return { from, to };
	}

	/**
	 * Update the resolved date range display text.
	 */
	function updateResolvedDateRange() {
		const range = getDateRange();
		if (range) {
			resolvedDisplay.textContent = `${formatDate(range.from)} \u2013 ${formatDate(range.to)}`;
		} else {
			resolvedDisplay.textContent = '';
		}
	}

	/**
	 * Toggle visibility of custom date inputs.
	 */
	function toggleCustomDates() {
		if (!dateRangeSelect || !customDates) {
			return;
		}
		const isCustom = dateRangeSelect.value === 'custom';
		customDates.style.display = isCustom ? 'flex' : 'none';
	}

	/**
	 * Format a Date object to a YYYY-MM-DD string in local time.
	 */
	function toLocalDateString(date) {
		const year = date.getFullYear();
		const month = String(date.getMonth() + 1).padStart(2, '0');
		const day = String(date.getDate()).padStart(2, '0');
		return `${year}-${month}-${day}`;
	}

	/**
	 * Refresh analytics sections when filters change.
	 */
	function refreshAnalytics() {
		updateResolvedDateRange();

		const range = getDateRange();
		const listId = listFilter ? listFilter.value : '';

		if (!range) {
			return;
		}

		// Validate that "from" date is not after "to" date.
		if (range.from > range.to) {
			return;
		}

		// Dispatch a custom event so other scripts can listen for filter changes.
		const event = new CustomEvent('mailchimp-analytics-refresh', {
			detail: {
				from: toLocalDateString(range.from),
				to: toLocalDateString(range.to),
				listId,
			},
		});
		document.dispatchEvent(event);
	}

	// Bind events.
	if (dateRangeSelect) {
		dateRangeSelect.addEventListener('change', function () {
			toggleCustomDates();
			refreshAnalytics();
		});
	}

	if (dateFrom) {
		dateFrom.addEventListener('change', refreshAnalytics);
	}

	if (dateTo) {
		dateTo.addEventListener('change', refreshAnalytics);
	}

	if (listFilter) {
		listFilter.addEventListener('change', refreshAnalytics);
	}

	// Initialize on load.
	toggleCustomDates();
	updateResolvedDateRange();
})();
