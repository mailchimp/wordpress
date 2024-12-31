import mailchimp from './mailchimpApiConfig';

/**
 * Mailchimp API requests and commands
 * 
 * NOTE: Intentionally not caching responses
 * - Tests change over time and flexibility should be a priority
 * - Caching could create false outcomes in tests that are hard to troubleshoot or be undetectable
 * - These functions are not run enough to warrant caching
 */

/**
 * Health Check
 */
Cypress.Commands.add('checkMailchimpApi', checkMailchimpApi);
async function checkMailchimpApi() {
  const response = await mailchimp.ping.get(); // Returns 'Everything\'s Chimpy!'
  if (response.health_status !== 'Everything\'s Chimpy!') {
      throw new Error('Mailchimp API is not working');
  }
}

/**
 * Get all Mailchimp lists from a users account
 * 
 * Gets lists from the account of the API token set in the mailchimp config
 */
Cypress.Commands.add('getMailchimpLists', getAllLists);
async function getAllLists() {
  const response = await mailchimp.lists.getAllLists();
  return response.lists;
}

/**
 * Get all Mailchimp lists from a users account
 * 
 * Gets lists from the account of the API token set in the mailchimp config
 */
Cypress.Commands.add('getContactsFromAList', getContactsFromAList);
async function getContactsFromAList(listId) {
  const response = await mailchimp.lists.getListMembersInfo(listId);
  return response.members;
}
