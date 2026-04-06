<?php
/**
 * Analytics page template
 *
 * @package Mailchimp
 */

$lists        = get_option( 'mailchimp_sf_lists', array() );
$current_list = get_option( 'mc_list_id', '' );
$dc           = get_option( 'mc_datacenter', '' );
?>
<div id="mailchimp-sf-settings-page">
	<?php include_once MCSF_DIR . 'includes/admin/templates/header.php'; // phpcs:ignore PEAR.Files.IncludingFile.UseRequireOnce ?>

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
					<label><?php esc_html_e( 'Date range', 'mailchimp' ); ?></label>
					<div class="mailchimp-sf-date-picker">
						<button type="button" class="mailchimp-sf-date-picker-trigger" id="mailchimp-sf-date-picker-trigger">
							<span id="mailchimp-sf-date-picker-label"><?php esc_html_e( 'Last 30 days', 'mailchimp' ); ?></span>
							<span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span>
						</button>
						<div class="mailchimp-sf-date-picker-popover" id="mailchimp-sf-date-picker-popover">
							<div class="mailchimp-sf-date-picker-popover-row">
								<div class="mailchimp-sf-date-picker-field">
									<label for="mailchimp-sf-date-range"><?php esc_html_e( 'Date range', 'mailchimp' ); ?></label>
									<select id="mailchimp-sf-date-range">
										<option value="7"><?php esc_html_e( 'Last 7 days', 'mailchimp' ); ?></option>
										<option value="30" selected><?php esc_html_e( 'Last 30 days', 'mailchimp' ); ?></option>
										<option value="90"><?php esc_html_e( 'Last 90 days', 'mailchimp' ); ?></option>
										<option value="180"><?php esc_html_e( 'Last 6 months', 'mailchimp' ); ?></option>
										<option value="365"><?php esc_html_e( 'Last year', 'mailchimp' ); ?></option>
										<option value="custom"><?php esc_html_e( 'Custom', 'mailchimp' ); ?></option>
									</select>
								</div>
								<div class="mailchimp-sf-date-picker-field">
									<label for="mailchimp-sf-date-from"><?php esc_html_e( 'Start date', 'mailchimp' ); ?></label>
									<input type="date" id="mailchimp-sf-date-from" />
								</div>
								<div class="mailchimp-sf-date-picker-field">
									<label for="mailchimp-sf-date-to"><?php esc_html_e( 'End date', 'mailchimp' ); ?></label>
									<input type="date" id="mailchimp-sf-date-to" />
								</div>
							</div>
							<div class="mailchimp-sf-date-picker-actions">
								<button type="button" class="mailchimp-sf-date-picker-cancel" id="mailchimp-sf-date-picker-cancel">
									<?php esc_html_e( 'Cancel', 'mailchimp' ); ?>
								</button>
								<button type="button" class="mailchimp-sf-date-picker-apply" id="mailchimp-sf-date-picker-apply">
									<?php esc_html_e( 'Apply', 'mailchimp' ); ?>
								</button>
							</div>
						</div>
					</div>
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
			</div>

			<div class="mailchimp-sf-analytics-content" id="mailchimp-sf-analytics-content">
				<?php
				$analytics_data   = new Mailchimp_Analytics_Data();
				$current_timestamp = current_time( 'timestamp' );
				$end_date          = wp_date( 'Y-m-d', $current_timestamp );
				$start_date        = wp_date( 'Y-m-d', $current_timestamp - ( 30 * DAY_IN_SECONDS ) );

				$totals = $analytics_data->get_totals( $current_list, $start_date, $end_date );
				$daily  = $analytics_data->get_analytics_data( $current_list, $start_date, $end_date );
				?>
				<h3><?php esc_html_e( 'Totals (Last 30 days)', 'mailchimp' ); ?></h3>
				<?php if ( ! empty( $totals ) && is_array( $totals ) ) : ?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Metric', 'mailchimp' ); ?></th>
								<th><?php esc_html_e( 'Value', 'mailchimp' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $totals as $metric_key => $metric_value ) : ?>
								<tr>
									<td><?php echo esc_html( $metric_key ); ?></td>
									<td>
										<?php
										if ( is_array( $metric_value ) || is_object( $metric_value ) ) {
											echo esc_html( wp_json_encode( $metric_value ) );
										} else {
											echo esc_html( (string) $metric_value );
										}
										?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php esc_html_e( 'No totals data is currently available for this period.', 'mailchimp' ); ?></p>
				<?php endif; ?>

				<h3><?php esc_html_e( 'Daily Breakdown', 'mailchimp' ); ?></h3>
				<?php if ( ! empty( $daily ) && is_array( $daily ) ) : ?>
					<?php
					$first_row  = reset( $daily );
					$has_header = is_array( $first_row ) && ! empty( $first_row );
					?>
					<table class="widefat striped">
						<?php if ( $has_header ) : ?>
							<thead>
								<tr>
									<?php foreach ( array_keys( $first_row ) as $header_key ) : ?>
										<th><?php echo esc_html( $header_key ); ?></th>
									<?php endforeach; ?>
								</tr>
							</thead>
						<?php endif; ?>
						<tbody>
							<?php foreach ( $daily as $row ) : ?>
								<tr>
									<?php
									if ( is_array( $row ) ) {
										foreach ( $row as $cell_value ) {
											if ( is_array( $cell_value ) || is_object( $cell_value ) ) {
												$cell_output = wp_json_encode( $cell_value );
											} else {
												$cell_output = (string) $cell_value;
											}
											?>
											<td><?php echo esc_html( $cell_output ); ?></td>
											<?php
										}
									} else {
										if ( is_array( $row ) || is_object( $row ) ) {
											$row_output = wp_json_encode( $row );
										} else {
											$row_output = (string) $row;
										}
										?>
										<td><?php echo esc_html( $row_output ); ?></td>
									<?php } ?>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php esc_html_e( 'No daily analytics data is currently available for this period.', 'mailchimp' ); ?></p>
				<?php endif; ?>
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
