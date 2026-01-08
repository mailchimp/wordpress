<?php
/**
 * Displays a Interest group.
 *
 * @package Mailchimp
 */

$list_id    = $block->context['mailchimp/list_id'] ?? '';
$group_id   = $attributes['id'] ?? '';
$label      = $attributes['label'] ?? '';
$is_visible = $attributes['visible'] ?? false;

// Bail if we don't have a list ID or group ID.
if ( ! $list_id || ! $group_id || ! $is_visible ) {
	return;
}

$interest_groups = get_option( 'mailchimp_sf_interest_groups_' . $list_id, array() );
$interest_groups = array_filter(
	$interest_groups,
	function ( $group ) use ( $group_id ) {
		return $group['id'] === $group_id;
	}
);

$interest_group = current( $interest_groups );
// Bail if we don't have a interest group.
if ( empty( $interest_group ) ) {
	return;
}

?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	if ( 'hidden' !== $interest_group['type'] ) {
		?>
		<div class="mc_interests_header">
			<?php echo wp_kses_post( $label ); ?>
		</div><!-- /mc_interests_header -->
		<div class="mc_interest">
		<?php
	} else {
		?>
		<div class="mc_interest" style="display: none;">
		<?php
	}

	mailchimp_interest_group_field( $interest_group );
	?>
	</div><!-- /mc_interest -->
</div>
