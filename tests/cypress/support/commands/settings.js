Cypress.Commands.add('selectList', (listName) => {
    cy.get('#mc_list_id').select(listName);
    cy.get('input[value="Update List"]').click();
});