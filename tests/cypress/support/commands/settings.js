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
	cy.get('#mc_list_id').select(listName, {force: true});
	cy.get('input[value="Update List"]').click();
});

/**
 * Custom Cypress command to enable or disable the JavaScript option in Mailchimp WordPress admin settings.
 *
 * This command visits the Mailchimp plugin settings page in the WordPress admin, 
 * toggles the "Use JavaScript" option based on the specified parameter, and 
 * updates the settings by submitting the form. It is helpful for testing scenarios 
 * that depend on JavaScript behavior in the plugin.
 *
 * @param {boolean} enabled - A flag to enable (`true`) or disable (`false`) the JavaScript option.
 *
 * @example
 * // Enable the JavaScript option
 * cy.setJavaScriptOption(true);
 *
 * @example
 * // Disable the JavaScript option
 * cy.setJavaScriptOption(false);
 */
Cypress.Commands.add('setJavaScriptOption', setJavaScriptOption);
function setJavaScriptOption(enabled) {
	cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
	if (enabled) {
		cy.get('#mc_use_javascript').check();
	} else {
		cy.get('#mc_use_javascript').uncheck();
	}
	cy.get('input[value="Update Subscribe Form Settings"]').first().click();
}

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
	cy.get('input[value="Update Subscribe Form Settings"]').first().click();
}

Cypress.Commands.add('setSettingsOption', setSettingsOption);
function setSettingsOption(selector, enabled) {
	cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
	if (enabled) {
		cy.get(selector).check();
	} else {
		cy.get(selector).uncheck();
	}
	cy.get('input[value="Update Subscribe Form Settings"]').first().click();
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

		cy.get('input[value="Update Subscribe Form Settings"]').first().click();
	});
}