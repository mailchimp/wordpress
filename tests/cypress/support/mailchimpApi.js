import mailchimp from '@mailchimp/mailchimp_marketing';

/**
 * Mailchimp API config
 * 
 * Used for E2E tests that require Mailchimp account
 * modification
 */
function mailchimpApiConfig() {
	mailchimp.setConfig({
        apiKey: Cypress.env('MAILCHIMP_API_KEY'),
        server: Cypress.env('MAILCHIMP_API_SERVER_PREFIX'),
	});
}

async function callPing() {
	const response = await mailchimp.ping.get();
	console.log(response);
}