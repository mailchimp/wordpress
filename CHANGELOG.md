# Changelog

All notable changes to this project will be documented in this file, per [the Keep a Changelog standard](http://keepachangelog.com/), and will adhere to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - TBD

## [1.6.0] - 2024-07-XX
**Note this version increased the WordPress minimum version to 6.1 and the PHP minimum version to 7.0.**

### Added
- Mailchimp List Subscribe Form custom block (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#35](https://github.com/mailchimp/wordpress/pull/35), [#38](https://github.com/mailchimp/wordpress/pull/38)).
- PHP 8.3 compatibility and minimum PHP version of 7.0 (props [@nateconley](https://github.com/nateconley), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#26](https://github.com/mailchimp/wordpress/pull/26)).
- WordPress 6.5 compatibility (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#27](https://github.com/mailchimp/wordpress/pull/27)).
- Created a `LICENSE.md` file and sets the license in the plugin to `GPL-2.0-or-later` (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#14](https://github.com/mailchimp/wordpress/pull/14)).
- Plugin icon and banner assets (props [@eddieshrake](https://github.com/eddieshrake), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#28](https://github.com/mailchimp/wordpress/pull/28)).

### Changed
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

### Developer
- Created a `README.md` file from the `readme.txt` content (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#14](https://github.com/mailchimp/wordpress/pull/14), [#22](https://github.com/mailchimp/wordpress/pull/22)).
- Created `CODE_OF_CONDUCT.md`, `CONTRIBUTING.md`, `SECURITY.md`, Issue and PR templates, Dependabot config file, and `composer.json` and `package.json` base files (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#15](https://github.com/mailchimp/wordpress/pull/15)).
- Added WordPress Version Checker and Build Release Zip GitHub Action Workflows (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#16](https://github.com/mailchimp/wordpress/pull/16)).
- Added Cypress E2E framework and associated GitHub Action Workflow (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#17](https://github.com/mailchimp/wordpress/pull/17)).
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
[1.6.1]: https://github.com/mailchimp/wordpress/compare/1.6.0...1.6.1
[1.6.0]: https://github.com/mailchimp/wordpress/tree/1.6.0
