const { defineConfig } = require( 'cypress' );
const { loadConfig } = require( '@wordpress/env/lib/config' );
const getCacheDirectory = require( '@wordpress/env/lib/config/get-cache-directory' );

module.exports = defineConfig( {
	chromeWebSecurity: false,
	fixturesFolder: 'tests/cypress/fixtures',
	screenshotsFolder: 'tests/cypress/screenshots',
	videosFolder: 'tests/cypress/videos',
	downloadsFolder: 'tests/cypress/downloads',
	video: true,
	reporter: 'mochawesome',
	reporterOptions: {
		mochaFile: 'mochawesome-[name]',
		reportDir: __dirname + '/reports',
		overwrite: false,
		html: false,
		json: true,
	},
	e2e: {
		setupNodeEvents( on, config ) {
			return setBaseUrl( on, config );
		},
		specPattern: [
			'tests/cypress/e2e/admin.test.js',
			'tests/cypress/e2e/connect.test.js',
			'tests/cypress/e2e/settings.test.js',
			'tests/cypress/e2e/mailchimp-block.test.js',
			'tests/cypress/e2e/logout.test.js',
		],
		supportFile: 'tests/cypress/support/index.js',
		defaultCommandTimeout: 20000,
	},
	retries: {
		runMode: 2,
		openMode: 0,
	},
} );

/**
 * Set WP URL as baseUrl in Cypress config.
 *
 * @param {Function} on     function that used to register listeners on various events.
 * @param {Object}   config Cypress Config object.
 * @return {Object} Updated Cypress Config object.
 */
const setBaseUrl = async ( on, config ) => {
	const cacheDirectory = await getCacheDirectory();
	const wpEnvConfig = await loadConfig( cacheDirectory );

	if ( wpEnvConfig ) {
		const port = wpEnvConfig.env.tests.port || null;

		if ( port ) {
			config.baseUrl = wpEnvConfig.env.tests.config.WP_SITEURL;
		}
	}

	return config;
};
