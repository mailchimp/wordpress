/**
 * Custom Cypress command to select a Mailchimp list in the WordPress admin settings.
 *
 * This command navigates to the Mailchimp plugin settings page in the WordPress admin,
 * selects a specified list from the dropdown, and submits the form to update the settings.
 * It is useful for setting up the test environment with the correct Mailchimp list.
 *
 * @param {string} listName - The name of the Mailchimp list to select.
 *
 * @example
 * // Select a Mailchimp list named "10up List"
 * cy.selectList('10up List');
 */
Cypress.Commands.add('selectList', (listName) => {
	cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
	cy.get('#mc_list_id option:selected')
		.invoke('text')
		.then((value) => {
			if (value === listName) {
				// Value matches, you can log or perform actions
				cy.log('Select has the expected value');
			} else {
				cy.get('#mc_list_id').select(listName, { force: true });
				cy.get('input[value="Fetch list settings"]').click();
			}
		});
});

/**
 * Custom Cypress command to enable or disable the Double Opt-In option in Mailchimp WordPress admin settings.
 *
 * This command visits the Mailchimp plugin settings page in the WordPress admin,
 * toggles the "Double Opt-In" option based on the specified parameter, and
 * updates the settings by submitting the form. It is useful for testing scenarios
 * that require configuring the Double Opt-In behavior in the plugin.
 *
 * @param {boolean} enabled - A flag to enable (`true`) or disable (`false`) the Double Opt-In option.
 *
 * @example
 * // Enable the Double Opt-In option
 * cy.setDoubleOptInOption(true);
 *
 * @example
 * // Disable the Double Opt-In option
 * cy.setDoubleOptInOption(false);
 */
Cypress.Commands.add('setDoubleOptInOption', setDoubleOptInOption);
function setDoubleOptInOption(enabled) {
	cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
	if (enabled) {
		cy.get('#mc_double_optin').check();
	} else {
		cy.get('#mc_double_optin').uncheck();
	}
	cy.get('#mc_double_optin').trigger('change');
	cy.get('input[value="Save Changes"]:visible').first().click();
}

Cypress.Commands.add('setSettingsOption', setSettingsOption);
function setSettingsOption(selector, enabled) {
	cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
	if (enabled) {
		cy.get(selector).check();
	} else {
		cy.get(selector).uncheck();
	}
	cy.get(selector).trigger('change');
	cy.get('input[value="Save Changes"]:visible').first().click();
}

/**
 * Custom Cypress command to enable or disable Mailchimp merge fields in the WordPress admin settings.
 *
 * This command visits the Mailchimp plugin settings page in the WordPress admin
 * and toggles the visibility of specified merge fields (e.g., First Name, Last Name, etc.)
 * based on the provided action. It ensures the specified fields exist before performing
 * the action and submits the form to save the changes.
 *
 * @param {string} action - The action to perform on each merge field, either "check" or "uncheck".
 *                          Use "check" to enable the fields and "uncheck" to disable them.
 *
 * @example
 * // Enable all merge fields
 * cy.toggleMergeFields('check');
 *
 * @example
 * // Disable all merge fields
 * cy.toggleMergeFields('uncheck');
 */
Cypress.Commands.add('toggleMergeFields', toggleMergeFields);
function toggleMergeFields(action) {
	// Load the fields from the fixture
	cy.fixture('mergeFields').then((fields) => {
		const mergeFields = Object.values(fields); // Extract field selectors as an array

		cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

		mergeFields.forEach((field) => {
			cy.get(field).should('exist')[action]();
		});

		cy.get('#mc_mv_FNAME').trigger('change');
		cy.get('input[value="Save Changes"]:visible').first().click();
	});
}

/**
 * Custom Cypress command to set all Mailchimp merge fields as required or optional in the WordPress admin settings.
 *
 * This command updates the merge fields for a specified Mailchimp list, setting their "required" status
 * in the Mailchimp account. It ensures the WordPress plugin reflects these changes by re-selecting the list
 * in the plugin settings.
 *
 * @param {boolean} required - A flag to set the merge fields as required (`true`) or optional (`false`).
 * @param {string} [listName='10up'] - The name of the Mailchimp list for which the merge fields will be updated.
 *
 * @example
 * // Set all merge fields to required
 * cy.setMergeFieldsRequired(true);
 *
 * @example
 * // Set all merge fields to optional
 * cy.setMergeFieldsRequired(false);
 *
 * @example
 * // Set merge fields for a specific list
 * cy.setMergeFieldsRequired(true, 'Custom List');
 */
Cypress.Commands.add('setMergeFieldsRequired', (required, listName = '10up', fields = []) => {
	// Set all merge fields to required in the Mailchimp test user account
	cy.getListId(listName).then((listId) => {
		cy.updateMergeFieldsByList(listId, { required }, fields).then(() => {
			cy.selectList(listName); // Ensure list is selected, refreshes Mailchimp data with WP
		});
	});
});

Cypress.Commands.add('deleteWPSubscriberUser', () => {
	// Set all merge fields to required in the Mailchimp test user account
	cy.wpCli('wp user list --role=subscriber --field=ID').then((response) => {
		if (response.stdout) {
			cy.wpCli(`wp user delete ${response.stdout?.replace(/\n/g, ' ')} --reassign=1`);
		}
	});
});