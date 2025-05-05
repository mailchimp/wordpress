#!/bin/bash
set -e

npm run env run tests-wordpress chmod -- -c ugo+w /var/www/html
npm run env run tests-cli wp rewrite structure '/%postname%/' -- --hard

wp-env run tests-cli wp post create --post_title='Mailchimp signup form – shortcode' --post_type=page --post_status=publish --post_author=1 --post_content='<!-- wp:shortcode -->[mailchimpsf_form]<!-- /wp:shortcode -->'
wp-env run tests-cli wp post create --post_title='Mailchimp signup form - Block' --post_type=page --post_status=publish --post_author=1 --post_content='<!-- wp:mailchimp/mailchimp /-->'
