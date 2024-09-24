=== Mailchimp List Subscribe Form ===
Contributors: Mailchimp
Tags:         mailchimp, email, newsletter, signup, marketing
Tested up to: 6.6
Stable tag:   1.6.0
License:      GPL-2.0-or-later
License URI:  https://spdx.org/licenses/GPL-2.0-or-later.html

Add a Mailchimp signup form block, widget, or shortcode to your WordPress site.

== Description ==

Use the Mailchimp List Subscribe plugin to quickly add a Mailchimp signup form block, widget, or shortcode to your WordPress site.

After installation, if you already have a Mailchimp account, you'll log in with that account and then proceed to configure settings.

If you don't have an account, you can create one directly in the plugin. After entering in all your personal details, you'll need to activate your account via an email that will be sent to you. Once done, you'll proceed to configure settings.

On the settings screen, you'll select your Mailchimp list, choose merge fields and groups, and configure other options. Once done, you can now add the block, widget, or shortcode to your site. Typically, installation and setup will take about 5-10 minutes, and absolutely everything can be done via the WordPress Setting GUI, with no file editing at all.

WordPress.com compatibility is limited to Business tier users only. [How to add a signup form if you have a WordPress.com site](https://mailchimp.com/help/ways-to-add-a-signup-form-in-wordpress/).

=== Access Token Encryption ===

Starting in version 1.6.0, authentication has changed to use OAuth. As part of this process, we retrieve an access token that can be used to make API requests. To provide a high-level of security, this access token is encrypted before being stored in the WordPress database. In order to ensure this access token can be decrypted when used, the plugin relies on certain security constants that should remain unchanged.

With no additional configuration, we use the standard `LOGGED_IN_KEY` and `LOGGED_IN_SALT` constants that are normally set in your site's `wp-config.php` file. Some sites make use of security plugins that rotate these constants on a periodic basis. When this happens, we won't be able to decrypt the access token and you’ll need to reconnect your Mailchimp account to generate a new access token.

To prevent such issues, it is recommended to define two additional constants in your site's `wp-config.php` file: `MAILCHIMP_SF_ENCRYPTION_KEY` and `MAILCHIMP_SF_ENCRYPTION_SALT`. These constants should consist of a combination of characters, preferably at least 32 characters long. Once set, these values should not be changed. For strong values, you can copy some of the values from [here](https://api.wordpress.org/secret-key/1.1/salt/) and use them. You'll end up with additional code like the following in your `wp-config.php` file:

`
define( 'MAILCHIMP_SF_ENCRYPTION_KEY', 'put your unique phrase here' );
define( 'MAILCHIMP_SF_ENCRYPTION_SALT', 'put your unique phrase here' );
`

If these constants are added after you've already authenticated with Mailchimp, you will need to reconnect your account. To avoid this, you can copy the values from `LOGGED_IN_KEY` and `LOGGED_IN_SALT` (if they exist) to `MAILCHIMP_SF_ENCRYPTION_KEY` and `MAILCHIMP_SF_ENCRYPTION_SALT` respectively.

== Frequently Asked Questions ==

= Can I have multiple forms on one page? =

No, only one form should exist per page, no matter the display type (block, widget, or shortcode).

= Why am I not seeing all my fields in my form? =

You need to ensure that the fields are enabled both in your Mailchimp account (Audience > Signup forms) and in the settings of this plugin.  Once the fields are enabled in both places, then they'll appear in the editor and frontend of your site.

= How can I translate “MailChimp List Subscribe Form”? =

Internationalization (i18n) is available on GlotPress at [https://translate.wordpress.org/projects/wp-plugins/mailchimp/](https://translate.wordpress.org/projects/wp-plugins/mailchimp/).  Any assistance [translating the plugin](https://translate.wordpress.org/projects/wp-plugins/mailchimp/) is greatly appreciated!

== Installation ==

This section describes how to install the plugin and get started using it.

= Version 2.8+ =

1. Unzip our archive and upload the entire mailchimp directory to your `/wp-content/plugins/ directory`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Navigate to the **Mailchimp** menu.
4. Click the Log in button and proceed through the OAuth flow, logging in to your Mailchimp account and authorizing the application.
5. Select the list where you want to send new Mailchimp subscribers.
6. Optional: Turn **Merge Fields** and **Groups** on or off. Navigate to **Appearance**, and click **Widgets**. Drag the Mailchimp Widget into one of your Widget Areas.
7. Optional: adjust frontend site display with available CSS options.

== Upgrading ==

If you are upgrading to version 1.2.1 and you used the widget in your sidebar previously, all you need to do is drag the `Mailchimp Widget` back into the sidebar, visit the Mailchimp settings page (which will have maintained your prior settings), click the "Update List" button, and you're done!

== Advanced ==

If you have a custom-coded sidebar or something that prevents enabling widgets through the WordPress GUI, complete these steps instead.

WordPress v2.8 or higher:
` [mailchimpsf_form] `

If you are adding it inside a php code block, add this:

` mailchimp_sf_signup_form(); `

Or, if you are adding it within HTML, use this:

`<?php mailchimp_sf_signup_form(); ?>`

Where ever you want it to show up.

== Screenshots ==

1. Mailchimp List Subscribe Form block within the block inserter.
2. Mailchimp Block default state.
3. Mailchimp List Subscribe Form block previewing sign up form headers, fields, and button.
4. Connecting your Mailchimp account to WordPress via OAuth.
5. Logged in to your Mailchimp account and selecting a list to connect to.
6. Configuring your Signup Form display format (optional).
7. Configuring extra fields on your Signup Form (optional).
8. CSS options for styling your Signup Form.
9. Creating a new Mailchimp account.

== Changelog ==

= 1.6.0 - 2024-09-24 =

**Note this version increased the WordPress minimum version to 6.1 and the PHP minimum version to 7.0.**

* **Added:** Mailchimp List Subscribe Form custom block (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#35](https://github.com/mailchimp/wordpress/pull/35), [#38](https://github.com/mailchimp/wordpress/pull/38)).
* **Added:** PHP 8.3 compatibility and minimum PHP version of 7.0 (props [@nateconley](https://github.com/nateconley), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#26](https://github.com/mailchimp/wordpress/pull/26)).
* **Added:** WordPress 6.5 compatibility (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#27](https://github.com/mailchimp/wordpress/pull/27)).
* **Added:** OAuth authentication for connecting a Mailchimp account with WordPress (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#47](https://github.com/mailchimp/wordpress/pull/47), [#48](https://github.com/mailchimp/wordpress/pull/48), [#50](https://github.com/mailchimp/wordpress/pull/50), [#52](https://github.com/mailchimp/wordpress/pull/52)).
* **Added:** New user signup flow, allowing users to sign up for a Mailchimp account from within the plugin (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#51](https://github.com/mailchimp/wordpress/pull/51)).
* **Added:** Created a `LICENSE.md` file and sets the license in the plugin to `GPL-2.0-or-later` (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#14](https://github.com/mailchimp/wordpress/pull/14)).
* **Added:** Plugin icon and banner assets (props [@eddieshrake](https://github.com/eddieshrake), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#28](https://github.com/mailchimp/wordpress/pull/28)).
* **Changed:** Bump WordPress "tested up to" version 6.6 (props [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@vikrampm1](https://github.com/vikrampm1), [@jeffpaul](https://github.com/jeffpaul) via [#43](https://github.com/mailchimp/wordpress/pull/43)).
* **Changed:** Ensure the signup form is only visible when a list is selected in the settings (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#54](https://github.com/mailchimp/wordpress/pull/54)).
* **Changed:** Moved settings page and link to top-level WP Admin menu item (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#29](https://github.com/mailchimp/wordpress/pull/29), [#33](https://github.com/mailchimp/wordpress/pull/33)).
* **Changed:** Admin styles to match updated Mailchimp brand guidelines (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@eddieshrake](https://github.com/eddieshrake) via [#31](https://github.com/mailchimp/wordpress/pull/31)).
* **Changed:** Replaced references of MailChimp to Mailchimp except where those references are explicitly functional code (to limit the additional class/function updates needed) (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#14](https://github.com/mailchimp/wordpress/pull/14)).
* **Changed:** Updated `readme.txt` and `mailchimp.php` header values to the WP.org standards (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#14](https://github.com/mailchimp/wordpress/pull/14)).
* **Changed:** Moved screenshots into a new `.wordpress-org` directory (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#14](https://github.com/mailchimp/wordpress/pull/14)).
* **Changed:** Moved translations to [GlotPress](https://translate.wordpress.org/projects/wp-plugins/mailchimp/) (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#39](https://github.com/mailchimp/wordpress/pull/39)).
* **Deprecated:** Function `mailchimpSF_signup_form` deprecated in favor of `mailchimp_sf_signup_form` (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter) via [#24](https://github.com/mailchimp/wordpress/pull/24)).
* **Deprecated:** jQuery scrollTo (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#27](https://github.com/mailchimp/wordpress/pull/27)).
* **Removed:** Support for WordPress < 6.1.1 (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#27](https://github.com/mailchimp/wordpress/pull/27)).
* **Removed:** Polyfills for WordPress < 2.8.0 (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#27](https://github.com/mailchimp/wordpress/pull/27)).
* **Removed:** Monkey Rewards integration (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#30](https://github.com/mailchimp/wordpress/pull/30)).
* **Removed:** IE-specific admin stylesheet (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@eddieshrake](https://github.com/eddieshrake) via [#31](https://github.com/mailchimp/wordpress/pull/31)).
* **Fixed:** Formatting from linting checks (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter) via [#23](https://github.com/mailchimp/wordpress/pull/23)).
* **Fixed:** Datepicker display on frontend (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#25](https://github.com/mailchimp/wordpress/pull/25)).
* **Fixed:** Accessibility for admin settings page (props [@nateconley](https://github.com/nateconley), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@eddieshrake](https://github.com/eddieshrake) via [#31](https://github.com/mailchimp/wordpress/pull/31)).
* **Fixed:** Properly handle the scenario where a non-valid list is updated (props [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@nateconley](https://github.com/nateconley), [@vikrampm1](https://github.com/vikrampm1) via [#40](https://github.com/mailchimp/wordpress/pull/40)).
* **Fixed:** Ensure the custom block and shortcode both have consistent spacing (props [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@nateconley](https://github.com/nateconley), [@vikrampm1](https://github.com/vikrampm1) via [#41](https://github.com/mailchimp/wordpress/pull/41)).
* **Fixed:** Address some PHP warnings when a form submission happens (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#54](https://github.com/mailchimp/wordpress/pull/54)).

= 1.5.8 - 2022-09-26 =
* **Changed:** Updated `readme.txt` link.
* **Fixed:* PHP warning for merge tags.
* **Fixed:** Double slashes on resources being loaded.

[View historical changelog details here](https://github.com/mailchimp/wordpress/blob/develop/CHANGELOG.md).

== Upgrade Notice ==

= 1.6.0 =
This version increased the WordPress minimum version to 6.1 and the PHP minimum version to 7.0.

= 1.5.5 =
If you are updating from v1.4.x, you will need to re-authorize with an API key.

= 1.5 =
Updates the Mailchimp API version, adds double/single opt-in toggle.

= 1.4.2 =
add customized wp_nonces functions for post-back behavior to fix 4.0 callbacks

= 1.4.1 =
Fix for checkbox weirdness on 3.8

= 1.4 =
Added Developer Mode "Kitchen Sink" to aid in styling without having to authenticate a Mailchimp account.

= 1.3 =
Now using OAuth flow within plugin for user authentication

Admin UI refresh

= 1.2.11 =
Merged pull request from https://github.com/headshift/wp-mailchimp adding additional translation strings.

= 1.2.10 =
Fixed submission error when apostrophes are present

= 1.2.8=
Fixes bug where entire phone numbers were only being deposited in the area code portion

= 1.2.6 =
Fixes major bug with "Settings" link on Plugins screen.

= 1.2.5 =
Added support for multiple interest groups, field formatting based on type and date picker.
