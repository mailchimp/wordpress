/**
 * Utility Functions for Cypress Tests
 *
 * This file contains miscellaneous utility JavaScript functions
 * intended for use in Cypress test cases. The functions in this file
 * are designed to be:
 *
 * - **Synchronous**: These functions should execute immediately and return values directly, avoiding Cypress's promise-like chaining.
 * - **Standalone**: These are plain JavaScript functions, not Cypress commands, to provide flexibility and simplicity.
 * - **Reusable**: Utility functions in this file should be reusable across different test cases without modification.
 *
 * Guidelines:
 * 1. Do not wrap these functions in Cypress commands or use `cy.*` calls within them.
 * 2. Ensure all functions remain synchronous to avoid unintended behavior in Cypress's execution flow.
 * 3. Functions should follow proper documentation for easy understanding and maintenance.
 *
 * Example Usage (Adjust for your file structure):
 * ```
 * import { generateRandomEmail } from '../../support/functions/utility';
 *
 * const email = generateRandomEmail('test');
 * cy.get('#email').type(email);
 * ```
 */

/**
 * Generates a random email address using the provided prefix.
 * 
 * NOTE: Keeping as JS function instead of Cypress command to avoid promises
 *
 * @param {string} prefix - The prefix to prepend to the generated email address.
 * @returns {string} A unique email address in the format: `${prefix}-unixtimestamp-${Date.now()}@10up.com`.
 *
 * @example
 * // Returns "test-unixtimestamp-1672531200000@10up.com"
 * const email = generateRandomEmail('test');
 * console.log(email);
 */
export function generateRandomEmail(prefix) {
	return `${prefix}-unixtimestamp-${Date.now()}@10up.com`;
}
