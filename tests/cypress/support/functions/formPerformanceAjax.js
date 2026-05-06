/**
 * Helpers for stubbing `mailchimp_sf_get_form_performance` admin-ajax calls.
 * fetch() uses FormData (multipart); match by substring or parsed fields.
 *
 * @param {object} req Cypress intercepted request
 * @returns {boolean}
 */
function isFormPerformanceRequest(req) {
	const action = 'mailchimp_sf_get_form_performance';
	const { body } = req;
	if (typeof body === 'string') {
		return body.includes(action);
	}
	if (body && typeof body === 'object') {
		if (body.action === action) {
			return true;
		}
	}
	return JSON.stringify(body ?? '').includes(action);
}

/**
 * Build a minimal successful form-performance payload for stubs.
 *
 * @param {object} overrides Partial payload.data from PHP aggregate().
 * @returns {object} wp_send_json_success-compatible inner `data` object.
 */
function buildSuccessData(overrides = {}) {
	return {
		interval: 'daily',
		data: [
			{
				key: '2026-04-01',
				label: 'Apr 1',
				views: 120,
				submissions: 24,
				conversion_rate: 20.0,
			},
		],
		total_views: 120,
		total_submissions: 24,
		total_conversion_rate: 20.0,
		// `total_subscribers` is sourced from the Mailchimp List API and
		// included in the same response so the Audience Overview KPI card
		// can render from a single fetch shared with Form Performance.
		total_subscribers: 5082,
		...overrides,
	};
}

/**
 * Wrap inner data as a WordPress `wp_send_json_success`-shaped response body.
 *
 * @param {object} data Inner success payload (from buildSuccessData).
 * @returns {object} Full JSON body for the browser.
 */
function wpJsonSuccess(data) {
	return {
		success: true,
		data,
	};
}

/**
 * Build a WordPress `wp_send_json_error`-shaped response body for stubs.
 *
 * @param {string} message Error message exposed to the UI.
 * @returns {object} Full JSON body for wp_send_json_error shape.
 */
function wpJsonError(message) {
	return {
		success: false,
		data: {
			message,
		},
	};
}

module.exports = {
	isFormPerformanceRequest,
	buildSuccessData,
	wpJsonSuccess,
	wpJsonError,
};
