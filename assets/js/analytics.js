/**
 * Mailchimp Analytics Page JavaScript
 *
 * @package Mailchimp
 */

( function () {
	'use strict';

	var dateRangeSelect = document.getElementById( 'mailchimp-sf-date-range' );
	var customDates = document.querySelector( '.mailchimp-sf-custom-dates' );
	var dateFrom = document.getElementById( 'mailchimp-sf-date-from' );
	var dateTo = document.getElementById( 'mailchimp-sf-date-to' );
	var listFilter = document.getElementById( 'mailchimp-sf-list-filter' );
	var resolvedDisplay = document.getElementById( 'mailchimp-sf-resolved-date-range' );
	var contentArea = document.getElementById( 'mailchimp-sf-analytics-content' );

	/**
	 * Format a Date object to a human-readable string.
	 *
	 * @param {Date} date
	 * @return {string}
	 */
	function formatDate( date ) {
		return date.toLocaleDateString( undefined, {
			year: 'numeric',
			month: 'short',
			day: 'numeric',
		} );
	}

	/**
	 * Get the resolved date range based on current filter selection.
	 *
	 * @return {{ from: Date, to: Date }|null}
	 */
	function getDateRange() {
		var value = dateRangeSelect.value;
		var to = new Date();
		var from;

		if ( value === 'custom' ) {
			if ( ! dateFrom.value || ! dateTo.value ) {
				return null;
			}
			from = new Date( dateFrom.value + 'T00:00:00' );
			to = new Date( dateTo.value + 'T23:59:59' );
			return { from: from, to: to };
		}

		var days = parseInt( value, 10 );
		from = new Date();
		from.setDate( from.getDate() - days );
		return { from: from, to: to };
	}

	/**
	 * Update the resolved date range display text.
	 */
	function updateResolvedDateRange() {
		var range = getDateRange();
		if ( range ) {
			resolvedDisplay.textContent = formatDate( range.from ) + ' \u2013 ' + formatDate( range.to );
		} else {
			resolvedDisplay.textContent = '';
		}
	}

	/**
	 * Toggle visibility of custom date inputs.
	 */
	function toggleCustomDates() {
		var isCustom = dateRangeSelect.value === 'custom';
		customDates.style.display = isCustom ? 'flex' : 'none';
	}

	/**
	 * Refresh analytics sections when filters change.
	 */
	function refreshAnalytics() {
		updateResolvedDateRange();

		var range = getDateRange();
		var listId = listFilter ? listFilter.value : '';

		if ( ! range ) {
			return;
		}

		// Dispatch a custom event so other scripts can listen for filter changes.
		var event = new CustomEvent( 'mailchimp-analytics-refresh', {
			detail: {
				from: range.from.toISOString(),
				to: range.to.toISOString(),
				listId: listId,
			},
		} );
		document.dispatchEvent( event );
	}

	// Bind events.
	if ( dateRangeSelect ) {
		dateRangeSelect.addEventListener( 'change', function () {
			toggleCustomDates();
			refreshAnalytics();
		} );
	}

	if ( dateFrom ) {
		dateFrom.addEventListener( 'change', refreshAnalytics );
	}

	if ( dateTo ) {
		dateTo.addEventListener( 'change', refreshAnalytics );
	}

	if ( listFilter ) {
		listFilter.addEventListener( 'change', refreshAnalytics );
	}

	// Initialize on load.
	toggleCustomDates();
	updateResolvedDateRange();
} )();
