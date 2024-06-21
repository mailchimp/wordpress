<?php
/**
 * Displays a signup form.
 *
 * @package Mailchimp
 */

?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	mailchimp_sf_signup_form();
	?>
</div>
