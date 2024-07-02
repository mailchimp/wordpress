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

### How can I translate “MailChimp List Subscribe Form”?

Internationalization (i18n) is available on GlotPress at [https://translate.wordpress.org/projects/wp-plugins/mailchimp/](https://translate.wordpress.org/projects/wp-plugins/mailchimp/).  Any assistance [translating the plugin](https://translate.wordpress.org/projects/wp-plugins/mailchimp/) is greatly appreciated!

## Support Level

**Active:** Mailchimp is actively working on this, and we expect to continue work for the foreseeable future including keeping tested up to the most recent version of WordPress.  Bug reports, feature requests, questions, and pull requests are welcome.
