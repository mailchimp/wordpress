/**
 * NOTE: All gmail API code lives here for now.
 * If the code expands then we'll separate it into files.
 */

import { google } from 'googleapis';

/**
 * Retrieves a new access token using the refresh token.
 *
 * This function sends a POST request to the OAuth2 token endpoint to exchange
 * the provided refresh token for a new access token.
 *
 * @returns {Promise<string>} A promise that resolves to the new access token.
 * @throws {Error} If the token refresh request fails or returns a non-OK status.
 */
async function getAccessToken() {
	// Replace with your client ID, client secret, and refresh token
	const CLIENT_ID = 'your-client-id.apps.googleusercontent.com';
	const CLIENT_SECRET = 'your-client-secret';
	const REFRESH_TOKEN = 'your-refresh-token';
	const refreshUrl = 'https://oauth2.googleapis.com/token';

	const params = new URLSearchParams({
		client_id: CLIENT_ID,
		client_secret: CLIENT_SECRET,
		refresh_token: REFRESH_TOKEN,
		grant_type: 'refresh_token',
	});

	try {
		const response = await fetch(refreshUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: params.toString(),
		});

		if (!response.ok) {
			throw new Error(`Failed to refresh token: ${response.status} ${await response.text()}`);
		}

		const data = await response.json();
		return data.access_token;
	} catch (error) {
		console.error('Error refreshing access token:', error);
		throw error;
	}
}

/**
 * Fetches emails from Gmail API based on a query string.
 *
 * @param {string} query - The search query to filter emails (e.g., "subject:Your Subject").
 * @returns {Promise<void>} A promise that resolves when the function completes.
 */
Cypress.Commands.add('fetchEmails, fetchEmails');
async function fetchEmails(query) {
	// const accessToken = await getAccessToken();

	// // Authorize the Gmail API client
	// const authClient = new google.auth.OAuth2();
	// authClient.setCredentials({ access_token: accessToken });

	const gmail = google.gmail({ version: 'v1', auth: authClient });

	try {
		// List messages with a specific query (e.g., "Confirm your subscription")
		const response = await gmail.users.messages.list({
			userId: 'me',
			q: query,
			key: 'AIzaSyC4l8YAVECquVGRJWQuFi8veM0jtBmuOf0'
		});

		if (response.data.messages && response.data.messages.length > 0) {
			console.log('Messages:', response.data.messages);

			// Fetch the content of the first email
			const messageId = response.data.messages[0].id;
			const message = await gmail.users.messages.get({
				userId: 'me',
				id: messageId,
			});

			console.log('Message Snippet:', message.data.snippet);
		} else {
			console.log('No messages found.');
		}
	} catch (error) {
		console.error('Error fetching emails:', error.message);
	}
}

// Run the function
// fetchEmails('subject:"Confirm your subscription"');
