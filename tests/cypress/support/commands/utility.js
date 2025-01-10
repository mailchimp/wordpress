/**
 * Checks if MAILCHIMP_USERNAME and MAILCHIMP_PASSWORD environment variables are set.
 * Stops the test execution with an error message if either is missing.
 */
Cypress.Commands.add('checkMailchimpEnv', () => {
	const username = Cypress.env('MAILCHIMP_USERNAME');
	const password = Cypress.env('MAILCHIMP_PASSWORD');
	const apiKey = Cypress.env('MAILCHIMP_API_KEY');
	const serverPrefix = Cypress.env('MAILCHIMP_API_SERVER_PREFIX');

	if (!username || !password || !apiKey || !serverPrefix) {
		const errorMessage = `
		[ERROR] Required environment variables are missing:
		MAILCHIMP_USERNAME: ${username ? `${username.slice(0, 3)}*****${username.slice(-4)}` : 'NOT SET'}
		MAILCHIMP_PASSWORD: ${password ? 'SET' : 'NOT SET'}
		MAILCHIMP_API_KEY: ${apiKey ? `${apiKey.slice(0, 3)}*****${apiKey.slice(-4)}` : 'NOT SET'}
		MAILCHIMP_API_SERVER_PREFIX: ${serverPrefix ? `${serverPrefix}` : 'NOT SET'}

		Please set these environment variables as described in the "E2E tests" section 
		of the readme or through your CI/CD environment to proceed.
		`;

		// Log the error message and stop the test
		Cypress.log({ name: 'Env Check', message: errorMessage });
		throw new Error(errorMessage);
	}

	cy.log('Environment variables for Mailchimp are correctly set.');
});

Cypress.Commands.add('generateRandomEmail', (prefix) => {
	return `${prefix}-unixtimestamp-${Date.now()}@10up.com`;
});