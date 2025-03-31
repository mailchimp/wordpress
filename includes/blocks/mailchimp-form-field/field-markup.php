<?php
/**
 * Displays a form field.
 *
 * @package Mailchimp
 */

$list_id                 = $block->context['mailchimp/list_id'] ?? '';
$show_required_indicator = $block->context['mailchimp/show_required_indicator'] ?? true;
$field_tag               = $attributes['tag'] ?? '';
$label                   = $attributes['label'] ?? '';
$is_visible              = $attributes['visible'] ?? false;
$num_fields              = $show_required_indicator ? 2 : 1;

// Bail if we don't have a list ID or field tag.
if ( ! $list_id || ! $field_tag ) {
	return;
}

$merge_fields = get_option( 'mailchimp_sf_merge_fields_' . $list_id, array() );

$merge_fields = array_filter(
	$merge_fields,
	function ( $field ) use ( $field_tag ) {
		return $field['tag'] === $field_tag;
	}
);

$merge_field = current( $merge_fields );
// Bail if we don't have a merge field.
if ( empty( $merge_field ) ) {
	return;
}

?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	echo mailchimp_form_field( $merge_field, $num_fields, $is_visible, $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Ignoring because form field is escaped in function
	?>
</div>
