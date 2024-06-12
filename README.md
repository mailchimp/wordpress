# Mailchimp List Subscribe Form

> Add a Mailchimp signup form widget to your WordPress site.

[![Support Level](https://img.shields.io/badge/support-active-green.svg?label=Support)](#support-level) [![GPL-2.0-or-later License](https://img.shields.io/github/license/mailchimp/wordpress?label=License)](https://github.com/mailchimp/wordpress/blob/develop/LICENSE.md) ![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/mailchimp?label=Version) ![WordPress Minimum](https://img.shields.io/wordpress/plugin/wp-version/mailchimp?label=WordPress%20minimum) ![PHP Minimum](https://img.shields.io/wordpress/plugin/required-php/mailchimp?label=PHP%20minimum) ![WordPress Tested Up To](https://img.shields.io/wordpress/plugin/tested/mailchimp?label=WordPress) [![E2E Cypress Tests](https://github.com/mailchimp/wordpress/actions/workflows/e2e.yml/badge.svg)](https://github.com/mailchimp/wordpress/actions/workflows/e2e.yml) [![PHP Compatibility](https://github.com/mailchimp/wordpress/actions/workflows/php-compat.yml/badge.svg)](https://github.com/mailchimp/wordpress/actions/workflows/php-compat.yml) [![PHP Linting](https://github.com/mailchimp/wordpress/actions/workflows/phpcs.yml/badge.svg)](https://github.com/mailchimp/wordpress/actions/workflows/phpcs.yml) [![JS Linting](https://github.com/mailchimp/wordpress/actions/workflows/eslint.yml/badge.svg)](https://github.com/mailchimp/wordpress/actions/workflows/eslint.yml) 

## Overview

Use the Mailchimp List Subscribe plugin to quickly add a Mailchimp signup form widget to your WordPress 2.8 or higher site.

![An example Signup Form Widget](https://github.com/mailchimp/wordpress/blob/develop/.wordpress-org/screenshot-5.jpg?raw=true)

After installation, you’ll log in with your API key, select your Mailchimp list, choose merge fields and groups, and add the widget to your site.  Typically, installation and setup will take about 5-10 minutes, and absolutely everything can be done via the WordPress Setting GUI, with no file editing at all.

![Configuring your Signup Form display format (optional)](https://github.com/mailchimp/wordpress/blob/develop/.wordpress-org/screenshot-3.jpg?raw=true)

WordPress.com compatibility is limited to Business tier users only. [How to add a signup form if you have a WordPress.com site](https://mailchimp.com/help/ways-to-add-a-signup-form-in-wordpress/).

![Configuring extra fields on your Signup Form (optional)](https://github.com/mailchimp/wordpress/blob/develop/.wordpress-org/screenshot-4.jpg?raw=true)

## Installation

This section describes how to install the plugin and get started using it.

### Version 2.8+

1. Unzip our archive and upload the entire mailchimp directory to your `/wp-content/plugins/ directory`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Navigate to **Settings** click **Mailchimp Setup**.
4. Enter your Mailchimp API Key and let the plugin verify it.
5. Select the list where you want to send new Mailchimp subscribers.
6. Optional: Turn **Merge Fields** and **Groups** on or off. Navigate to **Appearance**, and click **Widgets**. Drag the Mailchimp Widget into one of your Widget Areas.

![Selecting your Mailchimp list](https://github.com/mailchimp/wordpress/blob/develop/.wordpress-org/screenshot-2.jpg?raw=true)

### Advanced

If you have a custom coded sidebar or bells and whistles that prevent enabling widgets  through the WordPress GUI, complete these steps instead.

WordPress v2.8 or higher: 
` [mailchimpsf_form] `

If you are adding it inside a php code block, pop this in:

` mailchimp_sf_signup_form(); `

Or, if you are dropping it in between a bunch of HTML, use this:

`<?php mailchimp_sf_signup_form(); ?>`

Where ever you want it to show up.

## Upgrading

If you are upgrading to version 1.2.1 and you used the widget in your sidebar previously, all you need to do is drag the `Mailchimp Widget` back into the sidebar, visit the Mailchimp settings page (which will have maintained your prior settings), click the "Update List" button, and you're done!

If you are upgrading to version 1.6.0, you will need to updated any references to display function `mailchimpSF_signup_form` to `mailchimp_sf_signup_form`.

## Internationalization (i18n)

Currently we have the plugin configured so it can be translated and the following languages supported:

* bg_BG - Bulgarian in Bulgaria (thanks to [SiteGround](http://www.siteground.com/wordpress-hosting.htm) for contributing)
* cs_CZ - Czech in the Czech Republic (thanks to [Peter Kahoun](http://kahi.cz/) for contributing)
* da_DK - Danish in Denmark (thanks to Jan Lund for contributing)
* de_DE - German in Germany (thanks to Michael Jaekel for contributing)
* el_GR - Modern Greek in Greece (thanks to Ιωάννης Δημοφέρλιας (John Dimoferlias) for contributing)
* en_US - English in the U.S.
* es_CL - Spanish in Chile (thanks to Tomás Nader for contributing)
* es_ES - Spanish in Spain (thanks to [Claudia Mansilla](http://cricava.com/) for contributing)
* et_ET - Estonian in Estonia (thanks to [Helen Urbanik](http://www.motomaania.ee/) for contributing)
* fr_FR - French in France (thanks to [Maxime Toulliou](http://www.maximetoulliou.com/) for contributing)
* he_IL - Hebrew in Israel (thanks to [שגיב בית](http://www.sagive.co.il) for contributing)
* hu_HU - Hungarian in Hungary (thanks to Okostobi for contributing)
* it_IT - Italian in Italy (thanks to [Stefan Des](http://www.stefandes.com) for contributing)
* ko_KR - Korean (thanks to 백선기 (SK Baek)  for contributing)
* nb_NO - Norwegian (thanks to [Alexander Roterud aka Defrag](http://www.tigerpews.com) for contributing)
* nl_BE - Dutch (thanks to [Filip Stas](http://suddenelfilio.net/) for contributing)
* pt_BR - Portuguese in Brazil (thanks to Maria Manoela Porto for contributing)
* pt_PT - Portuguese in Portugal (thanks to [Tiago Faria](http://xroot.org) for contributing)
* ro_RO - Romanian in Romania (thanks to Alexandru Armin Roșu for contributing)
* ru_RU - Russian in the Russian Federation (thanks to [Илья](http://fatcow.com) for contributing)
* sv_SE - Swedish in Sweden (thanks to [Sebastian Johnsson](http://www.agiley.se/) for contributing)
* tr_TR - Turkish in Turkey (thanks to [Hakan E.](http://kazancexpert.com/) for contributing)

If your language is not listed above, feel free to create a translation. Here are the basic steps:

1. Copy "mailchimp_i18n-en_US.po" to "mailchimp_i18n-LANG_COUNTRY.po" - fill in LANG and COUNTRY with whatever you use for WPLANG in wp-config.php
2. Grab a translation editor. [POedit](http://www.poedit.net/) works for us
3. Translate each line - if you need some context, open up mailchimp.php and search for the line number or text
4. [Fork](http://help.github.com/fork-a-repo/) the [repository on github](https://github.com/mailchimp/wordpress)
5. [Clone](http://help.github.com/remotes/#clone) the _develop_ branch
6. Add the appropriately named files to the /po/ directory and edit the /readme.txt to include how you'd like to be attributed
7. Make a [pull request](http://help.github.com/send-pull-requests/)

## Support Level

**Active:** Mailchimp is actively working on this, and we expect to continue work for the foreseeable future including keeping tested up to the most recent version of WordPress.  Bug reports, feature requests, questions, and pull requests are welcome.
