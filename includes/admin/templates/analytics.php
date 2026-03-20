<?php
/**
 * Analytics page template
 *
 * @package Mailchimp
 */

$user         = get_option( 'mc_user' );
$is_logged_in = ! ( ! $user || ( ! get_option( 'mc_api_key' ) && ! mailchimp_sf_get_access_token() ) );
$lists        = get_option( 'mailchimp_sf_lists', array() );
$current_list = get_option( 'mc_list_id', '' );
$dc           = get_option( 'mc_datacenter', '' );
?>
<div id="mailchimp-sf-analytics-page">
	<?php include_once MCSF_DIR . 'includes/admin/templates/header.php'; ?>

	<div class="mailchimp-sf-settings-page-hero-wrapper">
		<div class="mailchimp-sf-settings-page-hero">
			<div class="mailchimp-sf-settings-page-hero-title-wrapper">
				<h1 class="mailchimp-sf-settings-page-hero-title">
					<?php esc_html_e( 'Analytics', 'mailchimp' ); ?>
				</h1>
				<p class="mailchimp-sf-settings-page-hero-description">
					<?php esc_html_e( 'View audience analytics and insights for your Mailchimp lists.', 'mailchimp' ); ?>
				</p>
			</div>
		</div>
	</div>

	<div class="wrap">
		<div class="mailchimp-sf-analytics-wrapper">
			<div class="mailchimp-sf-analytics-filters">
				<div class="mailchimp-sf-analytics-filter-group">
					<label for="mailchimp-sf-date-range"><?php esc_html_e( 'Date Range', 'mailchimp' ); ?></label>
					<select id="mailchimp-sf-date-range">
						<option value="7"><?php esc_html_e( 'Last 7 days', 'mailchimp' ); ?></option>
						<option value="30" selected><?php esc_html_e( 'Last 30 days', 'mailchimp' ); ?></option>
						<option value="90"><?php esc_html_e( 'Last 90 days', 'mailchimp' ); ?></option>
						<option value="180"><?php esc_html_e( 'Last 6 months', 'mailchimp' ); ?></option>
						<option value="365"><?php esc_html_e( 'Last year', 'mailchimp' ); ?></option>
						<option value="custom"><?php esc_html_e( 'Custom', 'mailchimp' ); ?></option>
					</select>
				</div>

				<div class="mailchimp-sf-analytics-filter-group mailchimp-sf-custom-dates" style="display: none;">
					<label for="mailchimp-sf-date-from"><?php esc_html_e( 'From', 'mailchimp' ); ?></label>
					<input type="date" id="mailchimp-sf-date-from" />
					<label for="mailchimp-sf-date-to"><?php esc_html_e( 'To', 'mailchimp' ); ?></label>
					<input type="date" id="mailchimp-sf-date-to" />
				</div>

				<div class="mailchimp-sf-analytics-filter-group">
					<label for="mailchimp-sf-list-filter"><?php esc_html_e( 'List', 'mailchimp' ); ?></label>
					<select id="mailchimp-sf-list-filter">
						<?php if ( ! empty( $lists ) ) : ?>
							<?php foreach ( $lists as $list ) : ?>
								<option value="<?php echo esc_attr( $list['id'] ); ?>" <?php selected( $list['id'], $current_list ); ?>>
									<?php echo esc_html( $list['name'] ); ?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>

				<div class="mailchimp-sf-analytics-date-display">
					<span id="mailchimp-sf-resolved-date-range"></span>
				</div>
			</div>

			<div class="mailchimp-sf-analytics-content" id="mailchimp-sf-analytics-content">
				<div class="mailchimp-sf-analytics-placeholder">
					<p><?php esc_html_e( 'Select a date range and list to view analytics.', 'mailchimp' ); ?></p>
				</div>
			</div>

			<?php if ( $dc ) : ?>
				<div class="mailchimp-sf-analytics-deep-link">
					<a href="<?php echo esc_url( 'https://' . $dc . '.admin.mailchimp.com/analytics/audience-analytics/' ); ?>"
					   target="_blank"
					   rel="noopener noreferrer"
					   class="mailchimp-sf-button btn-secondary">
						<?php esc_html_e( 'View detailed analytics in Mailchimp', 'mailchimp' ); ?>
						<span class="dashicons dashicons-external" aria-hidden="true"></span>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
