import mailchimp from './mailchimpConfig';

/**
 * Health Check
 */

export async function checkMailchimpApi() {
    const response = await callPing();
    if (response.health_status !== 'Everything\'s Chimpy!') {
        throw new Error('Mailchimp API is not working');
    }
    cy.log('Mailchimp API is working');
}

// Returns 'Everything\'s Chimpy!'
async function callPing() {
  const response = await mailchimp.ping.get();
  return response;
}