<?php
/**
 * Analytics page template
 *
 * @package Mailchimp
 */

$lists        = ( new Mailchimp_List_Subscribe_Form_Blocks() )->get_lists();
$current_list = get_option( 'mc_list_id', '' );
$dc           = get_option( 'mc_datacenter', '' );
?>
<div id="mailchimp-sf-settings-page">
	<?php include_once MCSF_DIR . 'includes/admin/templates/header.php'; // phpcs:ignore PEAR.Files.IncludingFile.UseRequireOnce ?>

	<div class="mailchimp-sf-settings-page-header-wrapper">
		<div class="mailchimp-sf-settings-page-header">
			<div class="mailchimp-sf-settings-page-header-title-wrapper">
				<h1 class="mailchimp-sf-settings-page-header-title">
					<?php esc_html_e( 'Analytics', 'mailchimp' ); ?>
				</h1>
			</div>
		</div>
	</div>

	<div class="wrap">
		<div class="mailchimp-sf-analytics-wrapper">
			<div class="mailchimp-sf-analytics-page">
				<hr class="wp-header-end" />
				<div class="mailchimp-sf-analytics-filters">
					<div class="mailchimp-sf-analytics-filter-group">
						<span
							id="mailchimp-sf-date-range-label"
							class="mailchimp-sf-analytics-filter-group__label"
						><?php esc_html_e( 'Date range', 'mailchimp' ); ?></span>
						<div class="mailchimp-sf-date-picker">
							<button
								type="button"
								class="mailchimp-sf-date-picker-trigger"
								id="mailchimp-sf-date-picker-trigger"
								aria-expanded="false"
								aria-haspopup="dialog"
								aria-controls="mailchimp-sf-date-picker-popover"
								aria-labelledby="mailchimp-sf-date-range-label mailchimp-sf-date-picker-label"
							>
								<span id="mailchimp-sf-date-picker-label"><?php esc_html_e( 'Last 30 days', 'mailchimp' ); ?></span>
								<div class="indicator-date-picker" aria-hidden="true">
									<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
										<path d="M20.133 3.891a2.977 2.977 0 00-2.119-.882h-1a1 1 0 10-2 0l-6-.009a1 1 0 00-1-1 1 1 0 00-1 1h-1a3 3 0 00-3 3v1.992l-.023 9.994a3 3 0 002.995 3l12 .018a3 3 0 003-3l.018-12a2.98 2.98 0 00-.871-2.113zm-14.124 1.1h1a1 1 0 102 0l6 .009a1 1 0 002 0h1a1 1 0 011 1v.987l-14-.021v-.987a1 1 0 011-.988zm11.982 14.018l-12-.018a1 1 0 01-1-1L5 8.977 19 9l-.013 9.012a1 1 0 01-.996.997z"></path>
									</svg>
								</div>
							</button>
							<div
								class="mailchimp-sf-date-picker-popover"
								id="mailchimp-sf-date-picker-popover"
								role="dialog"
								aria-labelledby="mailchimp-sf-date-range-label"
							>
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
										<div class="mailchimp-sf-date-picker-input-wrap">
											<input type="text" id="mailchimp-sf-date-from" />
											<span class="mailchimp-sf-date-picker-field-calendar" aria-hidden="true">
												<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
													<path d="M20.133 3.891a2.977 2.977 0 00-2.119-.882h-1a1 1 0 10-2 0l-6-.009a1 1 0 00-1-1 1 1 0 00-1 1h-1a3 3 0 00-3 3v1.992l-.023 9.994a3 3 0 002.995 3l12 .018a3 3 0 003-3l.018-12a2.98 2.98 0 00-.871-2.113zm-14.124 1.1h1a1 1 0 102 0l6 .009a1 1 0 002 0h1a1 1 0 011 1v.987l-14-.021v-.987a1 1 0 011-.988zm11.982 14.018l-12-.018a1 1 0 01-1-1L5 8.977 19 9l-.013 9.012a1 1 0 01-.996.997z"></path>
												</svg>
											</span>
										</div>
									</div>
									<div class="mailchimp-sf-date-picker-field">
										<label for="mailchimp-sf-date-to"><?php esc_html_e( 'End date', 'mailchimp' ); ?></label>
										<div class="mailchimp-sf-date-picker-input-wrap">
											<input type="text" id="mailchimp-sf-date-to" />
											<span class="mailchimp-sf-date-picker-field-calendar" aria-hidden="true">
												<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
													<path d="M20.133 3.891a2.977 2.977 0 00-2.119-.882h-1a1 1 0 10-2 0l-6-.009a1 1 0 00-1-1 1 1 0 00-1 1h-1a3 3 0 00-3 3v1.992l-.023 9.994a3 3 0 002.995 3l12 .018a3 3 0 003-3l.018-12a2.98 2.98 0 00-.871-2.113zm-14.124 1.1h1a1 1 0 102 0l6 .009a1 1 0 002 0h1a1 1 0 011 1v.987l-14-.021v-.987a1 1 0 011-.988zm11.982 14.018l-12-.018a1 1 0 01-1-1L5 8.977 19 9l-.013 9.012a1 1 0 01-.996.997z"></path>
												</svg>
											</span>
										</div>
									</div>
								</div>
								<div class="mailchimp-sf-date-picker-actions">
									<button type="button" class="mailchimp-sf-button btn-secondary btn-small mailchimp-sf-date-picker-cancel" id="mailchimp-sf-date-picker-cancel">
										<?php esc_html_e( 'Cancel', 'mailchimp' ); ?>
									</button>
									<button type="button" class="mailchimp-sf-button btn-primary btn-small mailchimp-sf-date-picker-apply" id="mailchimp-sf-date-picker-apply">
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

				<section
					class="mailchimp-sf-analytics-card mailchimp-sf-ao is-loading"
					data-section="audience-overview"
					aria-labelledby="mailchimp-sf-ao-title"
				>
					<header class="mailchimp-sf-analytics-card__header">
						<h2 id="mailchimp-sf-ao-title" class="mailchimp-sf-analytics-card__title">
							<?php esc_html_e( 'Audience Overview', 'mailchimp' ); ?>
						</h2>
						<p
							id="mailchimp-sf-ao-daterange"
							class="mailchimp-sf-analytics-card__subtitle"
							aria-live="polite"
						><?php esc_html_e( 'Loading audience overview…', 'mailchimp' ); ?></p>
					</header>

					<div
						id="mailchimp-sf-ao-error-banner"
						class="mailchimp-sf-ao__error-banner"
						role="alert"
						hidden
					>
						<span class="mailchimp-sf-ao__error-banner-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" width="20" height="20" focusable="false" aria-hidden="true">
								<circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/>
								<line x1="12" y1="7.5" x2="12" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
								<circle cx="12" cy="16.5" r="1" fill="currentColor"/>
							</svg>
						</span>
						<div class="mailchimp-sf-ao__error-banner-body">
							<p class="mailchimp-sf-ao__error-banner-title">
								<?php esc_html_e( 'Unable to load audience overview', 'mailchimp' ); ?>
							</p>
							<p
								id="mailchimp-sf-ao-error-message"
								class="mailchimp-sf-ao__error-banner-message"
							>
								<?php esc_html_e( 'Unable to load data for the selected date range. Please check your connection and try again.', 'mailchimp' ); ?>
							</p>
						</div>
						<button
							type="button"
							id="mailchimp-sf-ao-error-retry"
							class="mailchimp-sf-button btn-secondary btn-small mailchimp-sf-ao__error-banner-action"
						>
							<?php esc_html_e( 'Resolve error', 'mailchimp' ); ?>
						</button>
					</div>

					<dl class="mailchimp-sf-ao__metrics">
						<div class="mailchimp-sf-ao__metric">
							<dt class="mailchimp-sf-ao__metric-label">
								<?php esc_html_e( 'Total subscribers', 'mailchimp' ); ?>
							</dt>
							<dd
								id="mailchimp-sf-ao-total-subscribers"
								class="mailchimp-sf-ao__metric-value"
							>&mdash;</dd>
						</div>
						<div class="mailchimp-sf-ao__metric">
							<dt class="mailchimp-sf-ao__metric-label">
								<?php esc_html_e( 'Form views', 'mailchimp' ); ?>
							</dt>
							<dd
								id="mailchimp-sf-ao-views"
								class="mailchimp-sf-ao__metric-value"
							>&mdash;</dd>
						</div>
						<div class="mailchimp-sf-ao__metric">
							<dt class="mailchimp-sf-ao__metric-label">
								<?php esc_html_e( 'New submissions', 'mailchimp' ); ?>
							</dt>
							<dd
								id="mailchimp-sf-ao-submissions"
								class="mailchimp-sf-ao__metric-value"
							>&mdash;</dd>
						</div>
						<div class="mailchimp-sf-ao__metric">
							<dt class="mailchimp-sf-ao__metric-label">
								<?php esc_html_e( 'Conversion rate', 'mailchimp' ); ?>
							</dt>
							<dd
								id="mailchimp-sf-ao-rate"
								class="mailchimp-sf-ao__metric-value"
							>&mdash;</dd>
						</div>
					</dl>

				</section>

				<section
					class="mailchimp-sf-analytics-card mailchimp-sf-fp is-loading"
					data-section="form-performance"
					aria-labelledby="mailchimp-sf-fp-title"
				>
					<header class="mailchimp-sf-analytics-card__header">
						<h2 id="mailchimp-sf-fp-title" class="mailchimp-sf-analytics-card__title">
							<?php esc_html_e( 'List performance over time', 'mailchimp' ); ?>
						</h2>
						<p
							id="mailchimp-sf-fp-daterange"
							class="mailchimp-sf-analytics-card__subtitle"
							aria-live="polite"
						><?php esc_html_e( 'Loading form performance…', 'mailchimp' ); ?></p>
					</header>

					<div class="mailchimp-sf-fp__chart-heading">
						<h3 class="mailchimp-sf-fp__chart-title">
							<?php esc_html_e( 'List Activity', 'mailchimp' ); ?>
						</h3>
						<p class="mailchimp-sf-fp__chart-subtitle">
							<?php esc_html_e( 'All form views, submissions, and conversion rate over time', 'mailchimp' ); ?>
						</p>
					</div>

					<div
						id="mailchimp-sf-fp-error-banner"
						class="mailchimp-sf-fp__error-banner"
						role="alert"
						hidden
					>
						<span class="mailchimp-sf-fp__error-banner-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" width="20" height="20" focusable="false" aria-hidden="true">
								<circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/>
								<line x1="12" y1="7.5" x2="12" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
								<circle cx="12" cy="16.5" r="1" fill="currentColor"/>
							</svg>
						</span>
						<div class="mailchimp-sf-fp__error-banner-body">
							<p class="mailchimp-sf-fp__error-banner-title">
								<?php esc_html_e( 'Unable to load form performance', 'mailchimp' ); ?>
							</p>
							<p
								id="mailchimp-sf-fp-error-message"
								class="mailchimp-sf-fp__error-banner-message"
							>
								<?php esc_html_e( 'Unable to load data for the selected date range. Please check your connection and try again.', 'mailchimp' ); ?>
							</p>
						</div>
						<button
							type="button"
							id="mailchimp-sf-fp-error-retry"
							class="mailchimp-sf-button btn-secondary btn-small mailchimp-sf-fp__error-banner-action"
						>
							<?php esc_html_e( 'Resolve error', 'mailchimp' ); ?>
						</button>
					</div>

					<div class="mailchimp-sf-fp__body">
						<div class="mailchimp-sf-fp__chart">
							<div class="mailchimp-sf-fp__canvas-wrap">
								<div class="mailchimp-sf-fp__skeleton-bars" aria-hidden="true">
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
								</div>
								<canvas
									id="mailchimp-sf-fp-line"
									class="mailchimp-sf-fp__canvas"
									role="img"
									aria-label="<?php esc_attr_e( 'Form views, submissions, and conversion rate chart', 'mailchimp' ); ?>"
									aria-describedby="mailchimp-sf-fp-data-table"
								></canvas>
								<div
									id="mailchimp-sf-fp-overlay"
									class="mailchimp-sf-fp__overlay"
									role="status"
									aria-live="polite"
								><?php esc_html_e( 'Loading form performance…', 'mailchimp' ); ?></div>
							</div>
						</div>
						<div
							id="mailchimp-sf-fp-data-table"
							class="mailchimp-sf-fp__data-table screen-reader-text"
						></div>
					</div>
				</section>

				<section
					class="mailchimp-sf-analytics-card mailchimp-sf-sa is-loading"
					data-section="subscriber-activity"
					aria-labelledby="mailchimp-sf-sa-title"
				>
					<header class="mailchimp-sf-analytics-card__header">
						<h2 id="mailchimp-sf-sa-title" class="mailchimp-sf-analytics-card__title">
							<?php esc_html_e( 'Subscriber change over time', 'mailchimp' ); ?>
						</h2>
						<p
							id="mailchimp-sf-sa-daterange"
							class="mailchimp-sf-analytics-card__subtitle"
							aria-live="polite"
						><?php esc_html_e( 'Loading subscriber activity…', 'mailchimp' ); ?></p>
					</header>

					<div
						id="mailchimp-sf-sa-notice"
						class="mailchimp-sf-sa__notice"
						role="status"
						hidden
					></div>

					<div class="mailchimp-sf-sa__chart-heading">
						<h3 class="mailchimp-sf-sa__chart-title">
							<?php esc_html_e( 'Subscriber Count', 'mailchimp' ); ?>
						</h3>
						<p class="mailchimp-sf-sa__chart-subtitle">
							<?php esc_html_e( 'Subscribers gained vs lost', 'mailchimp' ); ?>
						</p>
					</div>

					<div
						id="mailchimp-sf-sa-error-banner"
						class="mailchimp-sf-sa__error-banner"
						role="alert"
						hidden
					>
						<span class="mailchimp-sf-sa__error-banner-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" width="20" height="20" focusable="false" aria-hidden="true">
								<circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/>
								<line x1="12" y1="7.5" x2="12" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
								<circle cx="12" cy="16.5" r="1" fill="currentColor"/>
							</svg>
						</span>
						<div class="mailchimp-sf-sa__error-banner-body">
							<p class="mailchimp-sf-sa__error-banner-title">
								<?php esc_html_e( 'API Disconnected', 'mailchimp' ); ?>
							</p>
							<p
								id="mailchimp-sf-sa-error-message"
								class="mailchimp-sf-sa__error-banner-message"
							>
								<?php esc_html_e( 'Unable to load data for the selected date range. Please check your connection and try again.', 'mailchimp' ); ?>
							</p>
						</div>
						<button
							type="button"
							id="mailchimp-sf-sa-error-retry"
							class="mailchimp-sf-button btn-secondary btn-small mailchimp-sf-sa__error-banner-action"
						>
							<?php esc_html_e( 'Resolve error', 'mailchimp' ); ?>
						</button>
					</div>

					<div class="mailchimp-sf-sa__body">
						<div class="mailchimp-sf-sa__chart">
							<div class="mailchimp-sf-sa__canvas-wrap">
								<div class="mailchimp-sf-sa__skeleton-bars" aria-hidden="true">
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
								</div>
								<canvas
									id="mailchimp-sf-sa-bar"
									class="mailchimp-sf-sa__canvas"
									role="img"
									aria-label="<?php esc_attr_e( 'Subscriber change bar chart', 'mailchimp' ); ?>"
									aria-describedby="mailchimp-sf-sa-data-table"
								></canvas>
								<div
									id="mailchimp-sf-sa-overlay"
									class="mailchimp-sf-sa__overlay"
									role="status"
									aria-live="polite"
								><?php esc_html_e( 'Loading subscriber activity…', 'mailchimp' ); ?></div>
							</div>
							<div
								id="mailchimp-sf-sa-data-table"
								class="mailchimp-sf-sa__data-table screen-reader-text"
							></div>
						</div>

						<aside class="mailchimp-sf-sa__totals" aria-labelledby="mailchimp-sf-sa-totals-title">
							<h3 id="mailchimp-sf-sa-totals-title" class="mailchimp-sf-sa__totals-title">
								<?php esc_html_e( 'Totals for the selected date range', 'mailchimp' ); ?>
							</h3>
							<div class="mailchimp-sf-sa__donut-wrap">
								<div class="mailchimp-sf-sa__skeleton-donut" aria-hidden="true"></div>
								<canvas
									id="mailchimp-sf-sa-donut"
									class="mailchimp-sf-sa__canvas"
									aria-hidden="true"
								></canvas>
								<div class="mailchimp-sf-sa__donut-center">
									<span id="mailchimp-sf-sa-net" class="mailchimp-sf-sa__net">&mdash;</span>
								</div>
							</div>
							<ul class="mailchimp-sf-sa__legend">
								<li class="mailchimp-sf-sa__legend-item is-new">
									<span class="mailchimp-sf-sa__legend-swatch" aria-hidden="true"></span>
									<span class="mailchimp-sf-sa__legend-label">
										<?php esc_html_e( 'New Subscribers', 'mailchimp' ); ?>
									</span>
									<span
										id="mailchimp-sf-sa-total-new"
										class="mailchimp-sf-sa__legend-value"
									>&mdash;</span>
								</li>
								<li class="mailchimp-sf-sa__legend-item is-unsub">
									<span class="mailchimp-sf-sa__legend-swatch" aria-hidden="true"></span>
									<span class="mailchimp-sf-sa__legend-label">
										<?php esc_html_e( 'Unsubscribes', 'mailchimp' ); ?>
									</span>
									<span
										id="mailchimp-sf-sa-total-unsubs"
										class="mailchimp-sf-sa__legend-value"
									>&mdash;</span>
								</li>
							</ul>
						</aside>
					</div>
				</section>

				<?php if ( $dc ) : ?>
					<div class="mailchimp-sf-analytics-deep-link">
						<a href="<?php echo esc_url( 'https://' . $dc . '.admin.mailchimp.com/analytics/audience-analytics/' ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							class="mailchimp-sf-button btn-primary btn-small">
							<?php esc_html_e( 'View detailed analytics in Mailchimp', 'mailchimp' ); ?>
							<span class="dashicons dashicons-external" aria-hidden="true"></span>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
