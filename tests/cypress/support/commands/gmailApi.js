/**
 * NOTE: All gmail API code lives here for now.
 * If the code expands then we'll separate it into files.
 */

import { gapi } from 'gapi-script';

// Constants
const CLIENT_ID = Cypress.env('GMAIL_CLIENT_ID');
const CLIENT_SECRET = Cypress.env('GMAIL_CLIENT_SECRET'); // TODO: Is this needed?
const API_KEY = 'YOUR_API_KEY';
const SCOPES = 'https://www.googleapis.com/auth/gmail.readonly';

// Initialize the Gmail API
function initializeGapi() {
	gapi.load('client:auth2', () => {
		gapi.client.init({
			apiKey: API_KEY,
			clientId: CLIENT_ID,
			discoveryDocs: ['https://www.googleapis.com/discovery/v1/apis/gmail/v1/rest'],
			scope: SCOPES,
		})
		.then(() => {
			console.log('GAPI initialized');
		})
		.catch((error) => {
			console.error('Error initializing GAPI:', error);
		});
	});
}

// Sign in the user
function signIn() {
	gapi.auth2.getAuthInstance()
		.signIn()
		.then(() => {
			console.log('User signed in');
			fetchMessagesBySubject('Important');
		})
		.catch((error) => {
			console.error('Error signing in:', error);
		});
}

// Fetch messages filtered by subject line
function fetchMessagesBySubject(subject) {
	gapi.client.gmail.users.messages.list({
		userId: 'me',
		q: `subject:${subject}`, // Gmail search query
	})
	.then((response) => {
		const messages = response.result.messages || [];
		console.log(`Found ${messages.length} messages with subject: "${subject}"`);
		messages.forEach((message) => {
			fetchMessageDetails(message.id);
		});
	})
	.catch((error) => {
		console.error('Error fetching messages:', error);
	});
}

// Fetch detailed message data
function fetchMessageDetails(messageId) {
	gapi.client.gmail.users.messages.get({
		userId: 'me',
		id: messageId,
	})
	.then((response) => {
		const message = response.result;
		console.log('Message:', message);
	})
	.catch((error) => {
		console.error('Error fetching message details:', error);
	});
}

// Initialize the script
document.addEventListener('DOMContentLoaded', () => {
	initializeGapi();

	document.getElementById('signInButton').addEventListener('click', () => {
		signIn();
	});
});
