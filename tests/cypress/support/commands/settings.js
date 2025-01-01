Cypress.Commands.add('selectList', (listName) => {
    cy.visit('/wp-admin/admin.php?page=mailchimp_sf_options');
    cy.get('#mc_list_id').select(listName);
    cy.get('input[value="Update List"]').click();
});