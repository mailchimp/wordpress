<?php
/**
 * Displays a signup form.
 *
 * @package Mailchimp
 */

$list_id    = $block->context['mailchimp/list_id'] ?? '';
$field_tag  = $attributes['tag'] ?? '';
$label      = $attributes['label'] ?? '';
$is_visible = $attributes['visible'] ?? false;

// Bail if we don't have a list ID or field tag.
if ( ! $list_id || ! $field_tag ) {
	return;
}

$merge_fields = get_option( 'mailchimp_sf_merge_fields_' . $list_id, array() );
$merge_fields = array_filter(
	$merge_fields,
	function( $field ) use ( $field_tag ) {
		return $field['tag'] === $field_tag;
	}
);

$merge_field  = current( $merge_fields );
// Bail if we don't have a merge field.
if ( empty( $merge_field ) ) {
	return;
}

// TODO: Update this to correct it;
$num_fields = count( $merge_fields );
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	var_dump($attributes);
	if ( ! $merge_field['public'] ) {
		echo '<div style="display:none;">' . mailchimp_form_field( $merge_field, $num_fields, $is_visible, $label ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Ignoring because form field is escaped in function
	} else {
		echo mailchimp_form_field( $merge_field, $num_fields, $is_visible, $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Ignoring because form field is escaped in function
	}
	?>
</div>
