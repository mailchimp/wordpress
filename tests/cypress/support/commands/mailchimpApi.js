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
 * @param {string} tag - The merge field tag (e.g. "FNAME", "PHONE", etc.)
 * @param {object} data - The data to update the merge field with - Docs: https://mailchimp.com/developer/marketing/api/list-merges/update-merge-field/
 */
Cypress.Commands.add('updateMergeFieldByTag', updateMergeFieldByTag);
async function updateMergeFieldByTag(listId, tag, data) {
  const mergeFields = await getMergeFields(listId);
  const field = mergeFields.find((field) => field.tag === tag); // Filter what we want by tag
  const response = await updateMergeField(listId, field.merge_id, field.name, data);
  console.log('Updated merge field:', response);
  return response;
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

/**
 * Delete a contact from a Mailchimp list
 *
 * @param {string} listId - The Mailchimp list ID
 * @param {string} email - The email address of the contact to delete
 * @returns {Promise} - A promise that resolves when the contact is successfully deleted
 *
 * This function deletes a contact from the specified Mailchimp list by using the MD5 hash
 * of the lowercase email address. Mailchimp requires this hashed value to uniquely identify
 * contacts.
 */
Cypress.Commands.add('deleteContact', deleteContact);
async function deleteContact(listId, email) {
  try {
      // Generate MD5 hash of the lowercase email address
      const emailHash = require('crypto')
          .createHash('md5')
          .update(email.toLowerCase())
          .digest('hex');

      // Delete the contact from the list
      await mailchimp.lists.deleteListMember(listId, emailHash);
      console.log(`Successfully deleted contact: ${email}`);
  } catch (error) {
      console.error('Error deleting contact:', error.response ? error.response.body : error.message);
  }
}

/**
 * Subscribe an email to a Mailchimp list
 *
 * @param {string} listId - The Mailchimp list ID
 * @param {string} email - The email address to subscribe
 * @param {object} mergeFields - (Optional) Merge fields (e.g., { FNAME: 'John', LNAME: 'Doe' })
 * @returns {Promise} - A promise that resolves when the subscription is successful
 */
Cypress.Commands.add('subscribeToList', subscribeToList);
async function subscribeToList(listId, email, mergeFields = {}) {
  try {
    // Subscribe the contact to the list
    const response = await mailchimp.lists.addListMember(listId, {
      email_address: email,
      status: 'subscribed', // 'subscribed', 'unsubscribed', 'pending', or 'cleaned'
      merge_fields: mergeFields, // Optional merge fields for personalization
    });

    console.log(`Successfully subscribed ${email} to list ${listId}`);
    return response;
  } catch (error) {
    console.error('Error subscribing email:', error.response ? error.response.body : error.message);
    throw new Error(`Failed to subscribe ${email} to list ${listId}`);
  }
}