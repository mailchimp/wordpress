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
async function getContactsFromAList(listId, status = null) {
  let members = [];
  let offset = 0;
  const count = 100; // Number of members to fetch per request (Mailchimp's limit is usually 1000 per page)

  while (true) {
    const options = {
      count,
      offset,
    };

    // Add status filter if provided
    if (status) {
      options.status = status;
    }

    const response = await mailchimp.lists.getListMembersInfo(listId, options);

    members = members.concat(response.members);

    // Break the loop if we've fetched all members
    if (members.length >= response.total_items) {
      break;
    }

    // Increment the offset for the next page
    offset += count;
  }

  return members;
}

/**
 * Retrieve a contact's details from a Mailchimp list.
 *
 * This function fetches the details of a specific contact from a Mailchimp list
 * using the MD5 hash of the email address. If the contact is not found, it returns `null`.
 * Logs and rethrows unexpected errors for visibility.
 *
 * @param {string} listId - The Mailchimp list ID to search within.
 * @param {string} email - The email address of the contact to retrieve.
 * @returns {Promise<object|null>} - A promise that resolves with the contact's details 
 *                                   if found, or `null` if the contact does not exist.
 * 
 * @throws {Error} - Throws an error for unexpected failures (non-404 errors).
 * 
 * Example:
 * cy.getContact(listId, 'user@example.com').then((contact) => {
 *   if (contact) {
 *     console.log('Contact found:', contact);
 *   } else {
 *     console.log('Contact not found.');
 *   }
 * });
 */
async function getContact(email, listId) {
  try {
    // Generate MD5 hash of the lowercase email address
    const emailHash = require('crypto')
      .createHash('md5')
      .update(email.toLowerCase())
      .digest('hex');

    // Fetch and return the contact details
    return await mailchimp.lists.getListMember(listId, emailHash);
  } catch (error) {
    if (error.response?.status === 404) {
      // Return null if the contact is not found
      return null;
    }
    // Log and rethrow other errors for visibility
    console.error('Error fetching contact:', error.response?.body || error.message);
    throw error;
  }
}

/**
 * Retrieve a contact's details from a Mailchimp list by its name.
 *
 * @param {string} email - The email address of the contact to retrieve.
 * @param {string} listName - The name of the Mailchimp list (default is '10up').
 * @returns {Promise<object|null>} - A promise that resolves to the contact details if found, or `null` if not found.
 */
Cypress.Commands.add('getContactFromList', getContactFromList);
function getContactFromList(email, listName = '10up') {
  return cy.getListId(listName).then((listId) => {
    return cy.wrap(getContact(email, listId)); // Wrap the promise to work with Cypress chaining
  });
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

// TODO: Can we implement batch synchronously?
// async function updateMergeFieldsByList(listId, data) {
//   const mergeFields = await getMergeFields(listId);

//   // Prepare batch operations
//   const operations = mergeFields.map((field) => ({
//     method: "PATCH", // HTTP method for updating merge fields
//     path: `/lists/${listId}/merge-fields/${field.merge_id}`, // API path for each merge field
//     body: JSON.stringify({
//       ...data,
//       name: field.name, // Keep existing name
//     }),
//   }));

//   try {
//     // Send the batch request
//     const response = await mailchimp.batches.start({
//       operations, // Array of operations
//     });

//     console.log("Batch operation initiated:", response);

//     return response;
//   } catch (error) {
//     console.error("Error starting batch operation:", error);
//     throw error;
//   }
// }

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
 * 
 * Mailchimp paginates merge fields
 */
async function getMergeFields(listId) {
  let mergeFields = [];
  let offset = 0;
  const count = 100; // Number of fields to fetch per request

  while (true) {
    const response = await mailchimp.lists.getListMergeFields(listId, {
      count,
      offset,
    });

    mergeFields = mergeFields.concat(response.merge_fields);

    // Break the loop if we've fetched all the merge fields
    if (mergeFields.length >= response.total_items) {
      break;
    }

    // Increment the offset for the next batch
    offset += count;
  }

  return mergeFields;
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
 * Wrapper function to delete a contact specifically from the "10up" Mailchimp list.
 *
 * This function wraps the generic `deleteContact` function and automatically
 * retrieves the list ID for the "10up" list. It simplifies the process of 
 * deleting contacts from this specific list by removing the need to manually 
 * provide the list ID.
 *
 * @param {string} email - The email address of the contact to delete
 */
Cypress.Commands.add('deleteContactFromList', deleteContactFromList);
function deleteContactFromList(email, listName = '10up') {
  cy.getListId(listName).then((listId) => {
    deleteContact(listId, email);
  });
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

/**
 * Subscribe an email to a Mailchimp list by its name.
 *
 * @param {string} email - The email address to subscribe.
 * @param {string} listName - The name of the Mailchimp list (default is '10up').
 */
Cypress.Commands.add('subscribeToListByName', subscribeToListByName);
function subscribeToListByName(email, listName = '10up') {
  cy.getListId(listName).then((listId) => {
    cy.subscribeToList(listId, email);
    console.log(`Successfully subscribed ${email} to list ${listName}`);
  });
}