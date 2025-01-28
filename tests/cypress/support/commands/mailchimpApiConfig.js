// TODO: Not sure this file belongs here, but no better place for it
import mailchimp from '@mailchimp/mailchimp_marketing';

/**
 * Mailchimp API Config
 * Ensures lazy configuration for E2E tests requiring
 * Mailchimp account modification.
 */

// Configure once
let isConfigured = false;

function ensureConfigured() {
  if (!isConfigured) {
	mailchimp.setConfig({
	  apiKey: Cypress.env('MAILCHIMP_API_KEY'),
	  server: Cypress.env('MAILCHIMP_API_SERVER_PREFIX'),
	});
	isConfigured = true; // Mark as configured
  }
}

// Proxy to intercept property access and ensure configuration
export default new Proxy(mailchimp, {
  get(target, prop) {
	ensureConfigured(); // Ensure configuration before accessing any property
	return target[prop]; // Return the original property
  },
});