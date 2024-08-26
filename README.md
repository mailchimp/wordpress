# Mailchimp List Subscribe Form

> Add a Mailchimp signup form block, widget, or shortcode to your WordPress site.

[![Support Level](https://img.shields.io/badge/support-active-green.svg?label=Support)](#support-level) [![GPL-2.0-or-later License](https://img.shields.io/github/license/mailchimp/wordpress?label=License)](https://github.com/mailchimp/wordpress/blob/develop/LICENSE.md) ![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/mailchimp?label=Version) ![WordPress Minimum](https://img.shields.io/wordpress/plugin/wp-version/mailchimp?label=WordPress%20minimum) ![PHP Minimum](https://img.shields.io/wordpress/plugin/required-php/mailchimp?label=PHP%20minimum) ![WordPress Tested Up To](https://img.shields.io/wordpress/plugin/tested/mailchimp?label=WordPress) [![E2E Cypress Tests](https://github.com/mailchimp/wordpress/actions/workflows/e2e.yml/badge.svg)](https://github.com/mailchimp/wordpress/actions/workflows/e2e.yml) [![PHP Compatibility](https://github.com/mailchimp/wordpress/actions/workflows/php-compat.yml/badge.svg)](https://github.com/mailchimp/wordpress/actions/workflows/php-compat.yml) [![PHP Linting](https://github.com/mailchimp/wordpress/actions/workflows/phpcs.yml/badge.svg)](https://github.com/mailchimp/wordpress/actions/workflows/phpcs.yml) [![JS Linting](https://github.com/mailchimp/wordpress/actions/workflows/eslint.yml/badge.svg)](https://github.com/mailchimp/wordpress/actions/workflows/eslint.yml)

## Overview

![Mailchimp List Subscribe Form block within the block inserter.](https://github.com/mailchimp/wordpress/blob/develop/.wordpress-org/screenshot-1.jpg?raw=true)

Use the Mailchimp List Subscribe plugin to quickly add a Mailchimp signup form block, widget, or shortcode to your WordPress site.

![Mailchimp Block default state.](https://github.com/mailchimp/wordpress/blob/develop/.wordpress-org/screenshot-2.jpg?raw=true)

After installation, you’ll log in with your API key, select your Mailchimp list, choose merge fields and groups, and add the block, widget, or shortcode to your site.  Typically, installation and setup will take about 5-10 minutes, and absolutely everything can be done via the WordPress Setting GUI, with no file editing at all.

![Mailchimp List Subscribe Form block previewing sign up form headers, fields, and button.](https://github.com/mailchimp/wordpress/blob/develop/.wordpress-org/screenshot-3.jpg?raw=true)

WordPress.com compatibility is limited to Business tier users only. [How to add a signup form if you have a WordPress.com site](https://mailchimp.com/help/ways-to-add-a-signup-form-in-wordpress/).

## Access Token Encryption

Starting in version 1.6.0, authentication has changed to use OAuth. As part of this process, we retrieve an access token that can be used to make API requests. To provide a high-level of security, this access token is encrypted before being stored in the WordPress database. In order to ensure this access token can be decrypted when used, the plugin relies on certain security constants that should remain unchanged.

With no additional configuration, we use the standard `LOGGED_IN_KEY` and `LOGGED_IN_SALT` constants that are normally set in your site's `wp-config.php` file. Some sites make use of security plugins that rotate these constants on a periodic basis. When this happens, we won't be able to decrypt the access token and you’ll need to reconnect your Mailchimp account to generate a new access token.

To prevent such issues, it is recommended to define two additional constants in your site's `wp-config.php` file: `MAILCHIMP_SF_ENCRYPTION_KEY` and `MAILCHIMP_SF_ENCRYPTION_SALT`. These constants should consist of a combination of characters, preferably at least 32 characters long. Once set, these values should not be changed. For strong values, you can copy some of the values from [here](https://api.wordpress.org/secret-key/1.1/salt/) and use them. You'll end up with additional code like the following in your `wp-config.php` file:

```php
define( 'MAILCHIMP_SF_ENCRYPTION_KEY', 'put your unique phrase here' );
define( 'MAILCHIMP_SF_ENCRYPTION_SALT', 'put your unique phrase here' );
```

If these constants are added after you've already authenticated with Mailchimp, you will need to reconnect your account. To avoid this, you can copy the values from `LOGGED_IN_KEY` and `LOGGED_IN_SALT` (if they exist) to `MAILCHIMP_SF_ENCRYPTION_KEY` and `MAILCHIMP_SF_ENCRYPTION_SALT` respectively.

## Installation

This section describes how to install the plugin and get started using it.

### Version 2.8+

1. Unzip our archive and upload the entire mailchimp directory to your `/wp-content/plugins/ directory`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Navigate to the **Mailchimp** menu.

![Connecting your Mailchimp account to WordPress via API Key.](https://github.com/mailchimp/wordpress/blob/develop/.wordpress-org/screenshot-4.jpg?raw=true)

4. Enter your Mailchimp API Key and let the plugin verify it.

![Logged in to your Mailchimp account and selecting a list to connect to.](https://github.com/mailchimp/wordpress/blob/develop/.wordpress-org/screenshot-5.jpg?raw=true)


5. Select the list where you want to send new Mailchimp subscribers.

![Configuring your Signup Form display format (optional).](https://github.com/mailchimp/wordpress/blob/develop/.wordpress-org/screenshot-6.jpg?raw=true)

6. Optional: Turn **Merge Fields** and **Groups** on or off. Navigate to **Appearance**, and click **Widgets**. Drag the Mailchimp Widget into one of your Widget Areas.

![Configuring extra fields on your Signup Form (optional).](https://github.com/mailchimp/wordpress/blob/develop/.wordpress-org/screenshot-7.jpg?raw=true)

7. Optional: adjust frontend site display with available CSS options.

![CSS options for styling your Signup Form.](https://github.com/mailchimp/wordpress/blob/develop/.wordpress-org/screenshot-8.jpg?raw=true)

## Upgrading

If you are upgrading to version 1.2.1 and you used the widget in your sidebar previously, all you need to do is drag the `Mailchimp Widget` back into the sidebar, visit the Mailchimp settings page (which will have maintained your prior settings), click the "Update List" button, and you're done!

If you are upgrading to version 1.6.0, you will need to updated any references to display function `mailchimpSF_signup_form` to `mailchimp_sf_signup_form`.

## Advanced Usage

If you have a custom-coded sidebar or something that prevent enabling widgets through the WordPress GUI, complete these steps instead.

WordPress v2.8 or higher:
` [mailchimpsf_form] `

If you are adding it inside a php code block, add this:

` mailchimp_sf_signup_form(); `

Or, if you are adding it within HTML, use this:

`<?php mailchimp_sf_signup_form(); ?>`

Where ever you want it to show up.

## Frequently Asked Questions

### Can I have multiple forms on one page?

No, only one form should exist per page, no matter the display type (block, widget, or shortcode).

### Why am I not seeing all my fields in my form?

You need to ensure that the fields are enabled both in your Mailchimp account (Audience > Signup forms) and in the settings of this plugin.  Once the fields are enabled in both places, then they'll appear in the editor and frontend of your site.

### How can I translate “MailChimp List Subscribe Form”?

Internationalization (i18n) is available on GlotPress at [https://translate.wordpress.org/projects/wp-plugins/mailchimp/](https://translate.wordpress.org/projects/wp-plugins/mailchimp/).  Any assistance [translating the plugin](https://translate.wordpress.org/projects/wp-plugins/mailchimp/) is greatly appreciated!

## E2E tests
The `tests` directory contains end-to-end tests for the project, utilizing Cypress to run tests in an environment created using wp-env.

### Pre-requisites
- Node.js v20
- Docker
- Create an account in [Mailchimp](https://mailchimp.com/)

### Run E2E tests in local
1. Run `npm install`.
2. Run `npm run build`.
3. Run `npm run env:start`.
4. Set Mailchimp credentials as environment variables:
    - run `export CYPRESS_MAILCHIMP_USERNAME="your mailchimp username"`
	- run `export CYPRESS_MAILCHIMP_PASSWORD="your mailchimp password"`
5. Run `npm run cypress:run`. You can also run `npm run cypress:open` to run tests in UI mode.

## Support Level

**Active:** Mailchimp is actively working on this, and we expect to continue work for the foreseeable future including keeping tested up to the most recent version of WordPress.  Bug reports, feature requests, questions, and pull requests are welcome.
