import mailchimp from './mailchimpConfig';

/**
 * Health Check
 */

export async function checkMailchimpApi() {
    const response = await mailchimp.ping.get(); // Returns 'Everything\'s Chimpy!'
    if (response.health_status !== 'Everything\'s Chimpy!') {
        throw new Error('Mailchimp API is not working');
    }
}

/**
 * Mailchimp API Requests
 * 
 * NOTE: Intentionally not caching responses
 * - Tests change over time and flexibility should be a priority
 * - Caching could create false outcomes in tests that are hard to troubleshoot or undetectable
 * - These functions are not run enough to warrant caching
 */
export async function getAllLists() {
  return await mailchimp.lists.getAllLists();
}

export async function getContactsFromAList(listId) {
  return await mailchimp.lists.getListMembersInfo(listId);
}