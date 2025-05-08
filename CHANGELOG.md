# Changelog

All notable changes to this project will be documented in this file, per [the Keep a Changelog standard](http://keepachangelog.com/), and will adhere to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - TBD

## [1.8.0] - 2025-05-08

**Note that this release bumps the WordPress minimum version from 6.3 to 6.4.**

### Added

- Honeypot and no-JS fields to help prevent spam (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#139](https://github.com/mailchimp/wordpress/pull/139)).
- Confirmation prompt before logging out the user (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#149](https://github.com/mailchimp/wordpress/pull/149)).
- A note advising users to set the website URL in the Mailchimp Audience settings (props [@iamdharmesh](https://github.com/iamdharmesh), [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#145](https://github.com/mailchimp/wordpress/pull/145)).

### Changed

- Bump WordPress "tested up to" version 6.8 (props [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@dkotter](https://github.com/dkotter) via [#148](https://github.com/mailchimp/wordpress/pull/148)).
- Bump WordPress minimum supported version from 6.3 to 6.4 (props [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@dkotter](https://github.com/dkotter) via [#148](https://github.com/mailchimp/wordpress/pull/148)).

## [1.7.0] - 2025-04-08

### Changed

- Enhance the Mailchimp List Subscribe Form block to allow for selecting an audience list, reorder fields, toggle field and group visibility, and various other improvements (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#126](https://github.com/mailchimp/wordpress/pull/126)).
- Plugin settings page success and error messages will now use WP admin notices (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@iamdharmesh](https://github.com/iamdharmesh) via [#85](https://github.com/mailchimp/wordpress/pull/85)).
- Renamed `mailchimp_sf_global_msg` function to `mailchimp_sf_frontend_msg` to differentiate functionality from WP admin notices that are used exclusively in the WP Admin (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@iamdharmesh](https://github.com/iamdharmesh) via [#85](https://github.com/mailchimp/wordpress/pull/85)).
- Make the form field visibility dependent on the WordPress "Include?" settings instead of the Mailchimp settings (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@MaxwellGarceau](https://github.com/MaxwellGarceau) via [#137](https://github.com/mailchimp/wordpress/pull/137)).

### Fixed

- Ensure that form data persists when validation fails (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@MaxwellGarceau](https://github.com/MaxwellGarceau) via [#130](https://github.com/mailchimp/wordpress/pull/130)).
- Display an error message informing users that they must subscribe through the hosted form if they were previously unsubscribed (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@MaxwellGarceau](https://github.com/MaxwellGarceau) via [#131](https://github.com/mailchimp/wordpress/pull/131), [#140](https://github.com/mailchimp/wordpress/pull/140)).

### Removed

- The "CSS Cheat Sheet" section from the Mailchimp settings page (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#126](https://github.com/mailchimp/wordpress/pull/126)).
- The "Use JavaScript Support?" option from the Mailchimp settings page (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#126](https://github.com/mailchimp/wordpress/pull/126)).
- The "Use JavaScript Datepicker?" option from the Mailchimp settings page (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#126](https://github.com/mailchimp/wordpress/pull/126)).
- The "Custom Styling" settings from the Mailchimp settings page (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#126](https://github.com/mailchimp/wordpress/pull/126)).
- The "Remove Mailchimp CSS" settings from the Mailchimp settings page (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#126](https://github.com/mailchimp/wordpress/pull/126)).

### Security

- Bump `express` from 4.21.0 to 4.21.2 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter) via [#125](https://github.com/mailchimp/wordpress/pull/125)).

### Developer

- New tests to cover E2E critical flows (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@iamdharmesh](https://github.com/iamdharmesh) via [#87](https://github.com/mailchimp/wordpress/pull/87), [#99](https://github.com/mailchimp/wordpress/pull/99)).
- Update existing tests to more robustly cover E2E critical flows. Modularized reusable testing code (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@iamdharmesh](https://github.com/iamdharmesh) via [#87](https://github.com/mailchimp/wordpress/pull/87), [#99](https://github.com/mailchimp/wordpress/pull/99)).
- Update all third-party actions our workflows rely on to use versions based on specific commit hashes (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@iamdharmesh](https://github.com/iamdharmesh) via [#128](https://github.com/mailchimp/wordpress/pull/128)).
- Prevent overwriting the release content with the body text provided in the GitHub Action workflow file (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter) via [#129](https://github.com/mailchimp/wordpress/pull/129)).

## [1.6.3] - 2025-01-30

### Added

- Transform the `mailchimp_sf_shortcode` shortcode to the Mailchimp List Subscribe Form block (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#84](https://github.com/mailchimp/wordpress/pull/84)).

### Removed

- Deprecated Sopresto code (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#98](https://github.com/mailchimp/wordpress/pull/98)).

### Fixed

- Fatal PHP error that would occur when the phone merge field was set to US format, but the merge field was not included in the Mailchimp plugin (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#108](https://github.com/mailchimp/wordpress/pull/108)).
- Bug causing merge fields on the Mailchimp WP admin page to incorrectly display as not required when they were, in fact, required (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#109](https://github.com/mailchimp/wordpress/pull/109)).
- Fix an issue that allows a user to select merge fields that were not selected as visible in the Mailchimp account (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#110](https://github.com/mailchimp/wordpress/pull/110)).
- Bug causing all new subscribers to receive a double opt-in email (props Nathan Tetzlaff, [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#115](https://github.com/mailchimp/wordpress/pull/115)).
- Bug causing contacts with any Mailchimp status (subscribed, unsubscribed, pending, etc.) to be able to submit the sign-up form even if "Update Existing Subscriber?" was disabled (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#115](https://github.com/mailchimp/wordpress/pull/115)).
- Pending contacts will now still be required to confirm their original confirmation email if they try to update their contact while "Update Existing Subscribers?" and "Double Opt-in" are both enabled (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#115](https://github.com/mailchimp/wordpress/pull/115)).

### Security

- Bump `nanoid` from 3.3.7 to 3.3.8 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh) via [#111](https://github.com/mailchimp/wordpress/pull/111)).

### Developer

- Add autoloading using composer (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#95](https://github.com/mailchimp/wordpress/pull/95)).
- Updated GitHub Action step for generating the release assets (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#97](https://github.com/mailchimp/wordpress/pull/97)).

## [1.6.2] - 2024-11-12

**Note that this release bumps the WordPress minimum version from 6.1 to 6.3.**

### Changed

- Bump WordPress "tested up to" version 6.7 (props [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@vikrampm1](https://github.com/vikrampm1), [@dkotter](https://github.com/dkotter) via [#71](https://github.com/mailchimp/wordpress/pull/71)).
- Bump WordPress minimum supported version from 6.1 to 6.3 (props [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@vikrampm1](https://github.com/vikrampm1), [@dkotter](https://github.com/dkotter) via [#71](https://github.com/mailchimp/wordpress/pull/71)).

### Fixed

- Ensure the field name is properly added to the error message when the email address is blank (props [@iamdharmesh](https://github.com/iamdharmesh), [@jerclarke](https://github.com/jerclarke), [@dkotter](https://github.com/dkotter) via [#74](https://github.com/mailchimp/wordpress/pull/74)).

### Developer

- Remove the Advanced Section from our readme files (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter) via [#77](https://github.com/mailchimp/wordpress/pull/77)).

## [1.6.1] - 2024-10-14

### Changed

- Improved migration flow from API Key to OAuth authentication for a better user experience (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#67](https://github.com/mailchimp/wordpress/pull/67)).
- Updated the menu SVG icon (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#65](https://github.com/mailchimp/wordpress/pull/65)).

### Fixed

- Ensure that the settings remain persistent between logging out of the account and logging in again (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#66](https://github.com/mailchimp/wordpress/pull/66)).

### Security

- Bump `serve-static` from 1.15.0 to 1.16.2 and `express` from 4.19.2 to 4.21.0 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#55](https://github.com/mailchimp/wordpress/pull/55)).
- Bump `webpack` from 5.91.0 to 5.94.0 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#59](https://github.com/mailchimp/wordpress/pull/59)).

## [1.6.0] - 2024-09-24

**Note this version increased the WordPress minimum version to 6.1 and the PHP minimum version to 7.0.**

### Added

- Mailchimp List Subscribe Form custom block (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#35](https://github.com/mailchimp/wordpress/pull/35), [#38](https://github.com/mailchimp/wordpress/pull/38)).
- PHP 8.3 compatibility and minimum PHP version of 7.0 (props [@nateconley](https://github.com/nateconley), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#26](https://github.com/mailchimp/wordpress/pull/26)).
- WordPress 6.6 compatibility (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#27](https://github.com/mailchimp/wordpress/pull/27)).
- OAuth authentication for connecting a Mailchimp account with WordPress (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#47](https://github.com/mailchimp/wordpress/pull/47), [#48](https://github.com/mailchimp/wordpress/pull/48), [#50](https://github.com/mailchimp/wordpress/pull/50), [#52](https://github.com/mailchimp/wordpress/pull/52)).
- New user signup flow, allowing users to sign up for a Mailchimp account from within the plugin (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#51](https://github.com/mailchimp/wordpress/pull/51)).
- Created a `LICENSE.md` file and sets the license in the plugin to `GPL-2.0-or-later` (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#14](https://github.com/mailchimp/wordpress/pull/14)).
- Plugin icon and banner assets (props [@eddieshrake](https://github.com/eddieshrake), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#28](https://github.com/mailchimp/wordpress/pull/28)).

### Changed

- Bump WordPress "tested up to" version 6.6 (props [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@vikrampm1](https://github.com/vikrampm1), [@jeffpaul](https://github.com/jeffpaul) via [#43](https://github.com/mailchimp/wordpress/pull/43)).
- Ensure the signup form is only visible when a list is selected in the settings (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#54](https://github.com/mailchimp/wordpress/pull/54)).
- Moved settings page and link to top-level WP Admin menu item (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#29](https://github.com/mailchimp/wordpress/pull/29), [#33](https://github.com/mailchimp/wordpress/pull/33)).
- Admin styles to match updated Mailchimp brand guidelines (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@eddieshrake](https://github.com/eddieshrake) via [#31](https://github.com/mailchimp/wordpress/pull/31)).
- Replaced references of MailChimp to Mailchimp except where those references are explicitly functional code (to limit the additional class/function updates needed) (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#14](https://github.com/mailchimp/wordpress/pull/14)).
- Updated `readme.txt` and `mailchimp.php` header values to the WP.org standards (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#14](https://github.com/mailchimp/wordpress/pull/14)).
- Moved screenshots into a new `.wordpress-org` directory (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#14](https://github.com/mailchimp/wordpress/pull/14)).
- Moved translations to [GlotPress](https://translate.wordpress.org/projects/wp-plugins/mailchimp/) (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#39](https://github.com/mailchimp/wordpress/pull/39)).

### Deprecated

- Function `mailchimpSF_signup_form` deprecated in favor of `mailchimp_sf_signup_form` (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter) via [#24](https://github.com/mailchimp/wordpress/pull/24)).
- jQuery scrollTo (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#27](https://github.com/mailchimp/wordpress/pull/27)).

### Removed

- Support for WordPress < 6.1.1 (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#27](https://github.com/mailchimp/wordpress/pull/27)).
- Polyfills for WordPress < 2.8.0 (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#27](https://github.com/mailchimp/wordpress/pull/27)).
- Monkey Rewards integration (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#30](https://github.com/mailchimp/wordpress/pull/30)).
- IE-specific admin stylesheet (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@eddieshrake](https://github.com/eddieshrake) via [#31](https://github.com/mailchimp/wordpress/pull/31)).

### Fixed

- Formatting from linting checks (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter) via [#23](https://github.com/mailchimp/wordpress/pull/23)).
- Datepicker display on frontend (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#25](https://github.com/mailchimp/wordpress/pull/25)).
- Accessibility for admin settings page (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@eddieshrake](https://github.com/eddieshrake) via [#31](https://github.com/mailchimp/wordpress/pull/31)).
- Properly handle the scenario where a non-valid list is updated (props [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@nateconley](https://github.com/nateconley), [@vikrampm1](https://github.com/vikrampm1) via [#40](https://github.com/mailchimp/wordpress/pull/40)).
- Ensure the custom block and shortcode both have consistent spacing (props [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@nateconley](https://github.com/nateconley), [@vikrampm1](https://github.com/vikrampm1) via [#41](https://github.com/mailchimp/wordpress/pull/41)).
- Address some PHP warnings when a form submission happens (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#54](https://github.com/mailchimp/wordpress/pull/54)).

### Developer

- Created a `README.md` file from the `readme.txt` content (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#14](https://github.com/mailchimp/wordpress/pull/14), [#22](https://github.com/mailchimp/wordpress/pull/22)).
- Created `CODE_OF_CONDUCT.md`, `CONTRIBUTING.md`, `SECURITY.md`, Issue and PR templates, Dependabot config file, and `composer.json` and `package.json` base files (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#15](https://github.com/mailchimp/wordpress/pull/15)).
- Added WordPress Version Checker and Build Release Zip GitHub Action Workflows (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#16](https://github.com/mailchimp/wordpress/pull/16)).
- Added Cypress E2E framework, associated GitHub Action Workflow, and E2E tests (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@iamdharmesh](https://github.com/iamdharmesh) via [#17](https://github.com/mailchimp/wordpress/pull/17), [#48](https://github.com/mailchimp/wordpress/pull/48)).
- Added PHP Compatibility GitHub Action Workflow (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#18](https://github.com/mailchimp/wordpress/pull/18)).
- Added PHPCS Linting GitHub Action Workflow (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#19](https://github.com/mailchimp/wordpress/pull/19)).
- Added ESLint GitHub Action Workflow (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#20](https://github.com/mailchimp/wordpress/pull/20)).
- Added Standard GitHub Action Workflows (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#21](https://github.com/mailchimp/wordpress/pull/21)).

## 1.5.8 - 2022-09-26

### Changed

- Updated `readme.txt` link.

### Fixed

- PHP warning for merge tags.
- Double slashes on resources being loaded.

## 1.5.7

- Fix undefined variable notice.
- Fix HTML submission message.

## 1.5.6

- Fixes short array notation which caused a fatal error in older PHP versions.

## 1.5.5

- Fix timeout error on activation.

## 1.5.4

- Set optional value for API wrapper.

## 1.5.3 - 2016-11-01

- Fix PHP7 compatibility issue
- Cut down on size of API requests for users with large lists.
- Fix CSS issue on removing Mailchimp style.

## 1.5.2 - 206-08-17

- General bugfixes for merge fields.
- When re-initializing, update merge field values.

## 1.5.1

- Bugfix for fatal error in Mailchimp lib.

## 1.5 - 2016-07-13

- Upgrade to Mailchimp API v3.0
- Remove OAuth2 middle layer and use Mailchimp API keys.
- Include double/single opt-in toggle.

## 1.4.2 - 2014-09-19

- Updated for WordPress 4.0 postback compatibility.

## 1.4.1 - 2013-11-21

- Update styles to be compatible with upcoming 3.8 wp-admin changes.

## 1.4 - 2013-11-19

- Developer Mode "Kitchen Sink" takes over plugin for local development.
- Developer Mode has filters of changeable content.
- Fix bug related to required US phone validation.

## 1.3 - 2013-10-03

- Now using OAuth flow for user authentication.
- Admin UI refresh.

## 1.2.14 - 2013-04-01

- Add link to edit profile within error when duplicate signup occurs.

## 1.2.13 - 2012-11-26

- Fixed bug preventing address fields from submitting correctly.

## 1.2.12 - 2012-10-23

- Update spanish language files (es_ES and es_MX).

## 1.2.11 - 2012-09-20

- Updating translation strings.

## 1.2.10 - 2012-08-27

- Invalid group fix.

## 1.2.9 - 2012-04-17

- Fixed bug where multiple checkbox type interest groups were returning an invalid error.
- Fixed bug where assets were not enqueueing properly if the plugin directory was not set to 'mailchimp'. Now supports any directory name.

## 1.2.8 - 2012-03-12

- Fixed bug where entire phone numbers were only being deposited in the area code portion.

## 1.2.7 - 2012-02-21

- CSS should now always load correctly on the front end.
- Adding Hebrew and Romanian language support.
- Updating translation contribution instructions.
- Tested version is now 3.3.1.

## 1.2.6 - 2011-11-10

- Fixed bug with "Settings" link appearing on all plugins (props Jason Lane).
- Resolved issue with unnecessary calls to the Mailchimp API during upgrade check.
- Resolved PHP warning when there weren't any interest groups.

## 1.2.5 - 2011-09-22

- Field formatting based on type
- Support for multiple interest groups (a data upgrade procedure must be run by visiting the WordPress dashboard).
- Added jQuery datepicker option to be used with dates.
- Added a handful of new translations.
- Fixing various PHP notices and deprecated functions (props Jeffry Ghazally).

## 1.2.4 - 2011-04-14

- Version bump for proper listing on wordpress.org.

## 1.2.3 - 2011-04-11

- Change mailchimp_sf_where_am_i() to use plugins_url() in place of WP_PLUGIN_URL to take SSL into account when delivering assets (props John LeBlanc).
- Update MCAPI wrapper to bring back PHP4 support (note: PHP 5.2 to be required starting with WordPress 3.2).

## 1.2.2 - 2011-02-09

- Change MCAPI wrapper to use a more unique class name, v1.3 of the API, and a much lighter client library.

## 1.2.1 - 2010-11-09

- Fixed internationalization path bug.
- Fixed instances where i18n functions weren't necessary in admin.
- Added more strings to be translated.

## 1.2 - 2010-10-14

- Recommended Upgrade, please see "Upgrading" section of readme.
- Security and various other improvements

[Unreleased]: https://github.com/mailchimp/wordpress/compare/main...develop
[1.8.0]: https://github.com/mailchimp/wordpress/compare/1.7.0...1.8.0
[1.7.0]: https://github.com/mailchimp/wordpress/compare/1.6.3...1.7.0
[1.6.3]: https://github.com/mailchimp/wordpress/compare/1.6.2...1.6.3
[1.6.2]: https://github.com/mailchimp/wordpress/compare/1.6.1...1.6.2
[1.6.1]: https://github.com/mailchimp/wordpress/compare/1.6.0...1.6.1
[1.6.0]: https://github.com/mailchimp/wordpress/tree/1.6.0
