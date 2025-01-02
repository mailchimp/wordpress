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
