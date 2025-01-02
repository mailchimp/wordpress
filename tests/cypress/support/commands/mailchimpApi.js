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
 * Get list ID from a list name
 */
Cypress.Commands.add('getListId', getListId);
async function getListId(listName) {
  const lists = await getAllLists();
  const list = lists.find((list) => list.name === listName);
  return list.id;
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

/**
 * Set all merge fields to required in the Mailchimp test user account
 *
 * TODO: Configuration this to use the batch endpoint. Is the /batch endpoint worth the lift?
 * https://mailchimp.com/developer/marketing/guides/run-async-requests-batch-endpoint/#make-a-batch-operations-request
 * 
 * @param {string} listId - The Mailchimp list ID
 * @param {object} data - The data to update the merge fields with - Docs: https://mailchimp.com/developer/marketing/api/list-merges/update-merge-field/
 * @returns {Promise} - A promise that resolves when all merge fields are updated
 */
Cypress.Commands.add('updateMergeFieldsByList', updateMergeFieldsByList);
async function updateMergeFieldsByList(listId, data) {
  const mergeFields = await getMergeFields(listId);
  const updatedMergeFields = mergeFields.map((field) => {
    return updateMergeField(listId, field.merge_id, field.name, data);
  });

  return await Promise.all(updatedMergeFields);
}

/**
 * Update merge field by tag
 *
 * @param {string} listId - The Mailchimp list ID
 * @param {string} name - The merge field tag (e.g. "FNAME", "PHONE", etc.)
 * @param {object} data - The data to update the merge field with - Docs: https://mailchimp.com/developer/marketing/api/list-merges/update-merge-field/
 */
Cypress.Commands.add('updateMergeFieldByTag', updateMergeFieldByTag);
async function updateMergeFieldByTag(listId, name, data) {
  const mergeFields = await getMergeFields(listId);
  const field = mergeFields.find((field) => field.tag === name); // Filter what we want by tag
  return await updateMergeField(listId, field.merge_id, name, data);
}

/**
 * Get all merge fields for a list
 */
async function getMergeFields(listId) {
  const response = await mailchimp.lists.getListMergeFields(listId);
  return response.merge_fields;
}

/**
 * Updates merge fields for a list
 */
async function updateMergeField(listId, mergeId, name, data) {
  return await mailchimp.lists.updateListMergeField(
    listId,
    mergeId,
    {
      ...data,
      name: name,
    }
  );
}
