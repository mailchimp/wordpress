Cypress.Commands.add('selectList', (listName) => {
	cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
	cy.get('#mc_list_id').select(listName);
	cy.get('input[value="Update List"]').click();
});

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

// Function to toggle merge fields
Cypress.Commands.add('toggleMergeFields', toggleMergeFields);
function toggleMergeFields(action) {
	// Merge fields array for reuse
	const mergeFields = [
		'#mc_mv_FNAME',
		'#mc_mv_LNAME',
		'#mc_mv_ADDRESS',
		'#mc_mv_BIRTHDAY',
		'#mc_mv_COMPANY',
		'#mc_mv_PHONE'
	];

	cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');

	mergeFields.forEach((field) => {
		cy.get(field).should('exist')[action]();
	});

	cy.get('input[value="Update Subscribe Form Settings"]').first().click();
}