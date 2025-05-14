=== Mailchimp List Subscribe Form ===
Contributors: Mailchimp
Tags:         mailchimp, email, newsletter, signup, marketing
Tested up to: 6.8
Stable tag:   1.8.0
License:      GPL-2.0-or-later
License URI:  https://spdx.org/licenses/GPL-2.0-or-later.html

Add a Mailchimp signup form block, widget, or shortcode to your WordPress site.

== Description ==

Use the Mailchimp List Subscribe plugin to quickly add a Mailchimp signup form block, widget, or shortcode to your WordPress site.

After installation, if you already have a Mailchimp account, you'll log in with that account and then proceed to configure settings.

If you don't have an account, you can create one directly in the plugin. After entering in all your personal details, you'll need to activate your account via an email that will be sent to you. Once done, you'll proceed to configure settings.

On the settings screen, you'll select your Mailchimp list, choose merge fields and groups, and configure other options. Once done, you can now add the block, widget, or shortcode (`[mailchimpsf_form]`) to your site. Typically, installation and setup will take about 5-10 minutes, and absolutely everything can be done via the WordPress Setting GUI, with no file editing at all.

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

= 1.8.0 - 2025-05-08 =
**Note that this release bumps the WordPress minimum version from 6.3 to 6.4.**

* **Added:** Honeypot and no-JS fields to help prevent spam (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#139](https://github.com/mailchimp/wordpress/pull/139)).
* **Added:** Confirmation prompt before logging out the user (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#149](https://github.com/mailchimp/wordpress/pull/149)).
* **Added:** A note advising users to set the website URL in the Mailchimp Audience settings (props [@iamdharmesh](https://github.com/iamdharmesh), [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#145](https://github.com/mailchimp/wordpress/pull/145)).
* **Changed:** Bump WordPress "tested up to" version 6.8 (props [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@dkotter](https://github.com/dkotter) via [#148](https://github.com/mailchimp/wordpress/pull/148)).
* **Changed:** Bump WordPress minimum supported version from 6.3 to 6.4 (props [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@dkotter](https://github.com/dkotter) via [#148](https://github.com/mailchimp/wordpress/pull/148)).

= 1.7.0 - 2025-04-08 =
* **Changed:** Enhance the Mailchimp List Subscribe Form block to allow for selecting an audience list, reorder fields, toggle field and group visibility, and various other improvements (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#126](https://github.com/mailchimp/wordpress/pull/126)).
* **Changed:** Plugin settings page success and error messages will now use WP admin notices (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@iamdharmesh](https://github.com/iamdharmesh) via [#85](https://github.com/mailchimp/wordpress/pull/85)).
* **Changed:** Renamed `mailchimp_sf_global_msg` function to `mailchimp_sf_frontend_msg` to differentiate functionality from WP admin notices that are used exclusively in the WP Admin (props [@MaxwellGarceau](https://github.com/MaxwellGarceau), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@iamdharmesh](https://github.com/iamdharmesh) via [#85](https://github.com/mailchimp/wordpress/pull/85)).
* **Changed:** Make the form field visibility dependent on the WordPress "Include?" settings instead of the Mailchimp settings (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@MaxwellGarceau](https://github.com/MaxwellGarceau) via [#137](https://github.com/mailchimp/wordpress/pull/137)).
* **Fixed:** Ensure that form data persists when validation fails (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@MaxwellGarceau](https://github.com/MaxwellGarceau) via [#130](https://github.com/mailchimp/wordpress/pull/130)).
* **Fixed:** Display an error message informing users that they must subscribe through the hosted form if they were previously unsubscribed (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@MaxwellGarceau](https://github.com/MaxwellGarceau) via [#131](https://github.com/mailchimp/wordpress/pull/131), [#140](https://github.com/mailchimp/wordpress/pull/140)).
* **Removed:** The "CSS Cheat Sheet" section from the Mailchimp settings page (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#126](https://github.com/mailchimp/wordpress/pull/126)).
* **Removed:** The "Use JavaScript Support?" option from the Mailchimp settings page (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#126](https://github.com/mailchimp/wordpress/pull/126)).
* **Removed:** The "Use JavaScript Datepicker?" option from the Mailchimp settings page (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#126](https://github.com/mailchimp/wordpress/pull/126)).
* **Removed:** The "Custom Styling" settings from the Mailchimp settings page (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#126](https://github.com/mailchimp/wordpress/pull/126)).
* **Removed:** The "Remove Mailchimp CSS" settings from the Mailchimp settings page (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#126](https://github.com/mailchimp/wordpress/pull/126)).
* **Security:** Bump `express` from 4.21.0 to 4.21.2 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter) via [#125](https://github.com/mailchimp/wordpress/pull/125)).

= 1.6.3 - 2025-01-30 =
* **Added:** Transform the `mailchimp_sf_shortcode` shortcode to the Mailchimp List Subscribe Form block (props [@MaxwellGarceau](https://github.com/qasumitbagthariya), [@jeffpaul](https://github.com/vikrampm1), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#84](https://github.com/mailchimp/wordpress/pull/84)).
* **Removed:** Deprecated Sopresto code (props [@MaxwellGarceau](https://github.com/qasumitbagthariya), [@jeffpaul](https://github.com/vikrampm1), [@dkotter](https://github.com/dkotter) via [#98](https://github.com/mailchimp/wordpress/pull/98)).
* **Fixed:** Fatal PHP error that would occur when the phone merge field was set to US format, but the merge field was not included in the Mailchimp plugin (props [@MaxwellGarceau](https://github.com/qasumitbagthariya), [@jeffpaul](https://github.com/vikrampm1), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#108](https://github.com/mailchimp/wordpress/pull/108)).
* **Fixed:** Bug causing merge fields on the Mailchimp WP admin page to incorrectly display as not required when they were, in fact, required (props [@MaxwellGarceau](https://github.com/qasumitbagthariya), [@jeffpaul](https://github.com/vikrampm1), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#109](https://github.com/mailchimp/wordpress/pull/109)).
* **Fixed:** Fix an issue that allows a user to select merge fields that were not selected as visible in the Mailchimp account (props [@MaxwellGarceau](https://github.com/qasumitbagthariya), [@jeffpaul](https://github.com/vikrampm1), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#110](https://github.com/mailchimp/wordpress/pull/110)).
* **Fixed:** Bug causing all new subscribers to receive a double opt-in email (props Nathan Tetzlaff, [@MaxwellGarceau](https://github.com/qasumitbagthariya), [@jeffpaul](https://github.com/vikrampm1), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#115](https://github.com/mailchimp/wordpress/pull/115)).
* **Fixed:** Bug causing contacts with any Mailchimp status (subscribed, unsubscribed, pending, etc.) to be able to submit the sign-up form even if "Update Existing Subscriber?" was disabled (props [@MaxwellGarceau](https://github.com/qasumitbagthariya), [@jeffpaul](https://github.com/vikrampm1), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#115](https://github.com/mailchimp/wordpress/pull/115)).
* **Fixed:** Pending contacts will now still be required to confirm their original confirmation email if they try to update their contact while "Update Existing Subscribers?" and "Double Opt-in" are both enabled (props [@MaxwellGarceau](https://github.com/qasumitbagthariya), [@jeffpaul](https://github.com/vikrampm1), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#115](https://github.com/mailchimp/wordpress/pull/115)).
* **Security:** Bump `nanoid` from 3.3.7 to 3.3.8 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh) via [#111](https://github.com/mailchimp/wordpress/pull/111)).

= 1.6.2 - 2024-11-12 =
**Note that this release bumps the WordPress minimum version from 6.1 to 6.3.**

* **Changed:** Bump WordPress "tested up to" version 6.7 (props [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@vikrampm1](https://github.com/vikrampm1), [@dkotter](https://github.com/dkotter) via [#71](https://github.com/mailchimp/wordpress/pull/71)).
* **Changed:** Bump WordPress minimum supported version from 6.1 to 6.3 (props [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@vikrampm1](https://github.com/vikrampm1), [@dkotter](https://github.com/dkotter) via [#71](https://github.com/mailchimp/wordpress/pull/71)).
* **Fixed:** Ensure the field name is properly added to the error message when the email address is blank (props [@iamdharmesh](https://github.com/iamdharmesh), [@jerclarke](https://github.com/jerclarke), [@dkotter](https://github.com/dkotter) via [#74](https://github.com/mailchimp/wordpress/pull/74)).

= 1.6.1 - 2024-10-14 =
* **Changed:** Improved migration flow from API Key to OAuth authentication for a better user experience (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#67](https://github.com/mailchimp/wordpress/pull/67)).
* **Changed:** Updated the menu SVG icon (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#65](https://github.com/mailchimp/wordpress/pull/65)).
* **Fixed:** Ensure that the settings remain persistent between logging out of the account and logging in again (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@qasumitbagthariya](https://github.com/qasumitbagthariya) via [#66](https://github.com/mailchimp/wordpress/pull/66)).
* **Security:** Bump `serve-static` from 1.15.0 to 1.16.2 and `express` from 4.19.2 to 4.21.0 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#55](https://github.com/mailchimp/wordpress/pull/55)).
* **Security:** Bump `webpack` from 5.91.0 to 5.94.0 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#59](https://github.com/mailchimp/wordpress/pull/59)).

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

= 1.8.0 =
This release bumps the WordPress minimum version from 6.3 to 6.4.

= 1.6.2 =
This release bumps the WordPress minimum version from 6.1 to 6.3.

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
