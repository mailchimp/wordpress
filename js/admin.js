/* eslint-disable no-console */
(function ($) {
	const params = window.mailchimp_sf_admin_params || {};
	const oauthBaseUrl = 'https://woocommerce.mailchimpapp.com';

	/**
	 * Open Mailchimp OAuth popup.
	 *
	 * @param {string} token - Token from the Oauth service.
	 */
	function openMailChimpOauthPopup(token) {
		const startUrl = `${oauthBaseUrl}/auth/start/${token}`;
		const width = 800;
		const height = 600;
		const screenSizes = window.screen || { width: 1024, height: 768 };
		const left = (screenSizes.width - width) / 2;
		const top = (screenSizes.height - height) / 4;
		const windowOptions = `toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=${
			width
		}, height=${height}, top=${top}, left=${left}, domain=${oauthBaseUrl.replace('https://', '')}`;

		// Open Mailchimp OAuth popup.
		const popup = window.open(startUrl, params.oauth_window_name, windowOptions);

		if (popup == null) {
			// TODO: Handle popup blocked.
			console.error('Popup blocked. Please enable popups for this site.');
		} else {
			// Handle popup opened.
			const oauthInterval = window.setInterval(function () {
				if (popup.closed) {
					// Clear interval.
					window.clearInterval(oauthInterval);
					// TODO: Hide/show error/loading messages.

					// Check status of OAuth connection.
					$.post(`${oauthBaseUrl}/api/status/${token}`, function (statusData) {
						if (statusData && statusData.status === 'accepted') {
							const finishData = {
								action: 'mailchimp_sf_oauth_finish',
								nonce: params.oauth_finish_nonce,
								token,
							};

							// Finish OAuth connection and save token.
							$.post(params.ajax_url, finishData, function (finishResponse) {
								if (finishResponse.success) {
									// Token is saved in the database, reload the page to reflect the changes.
									window.location.reload();
								} else {
									console.log(
										'Error calling OAuth finish endpoint. Data:',
										finishResponse,
									);
								}
							}).fail(function () {
								console.error('Error calling OAuth finish endpoint.');
							});
						} else {
							console.log(
								'Error calling OAuth status endpoint. No credentials provided at login popup? Data:',
								statusData,
							);
						}
					}).fail(function () {
						console.error('Error calling OAuth status endpoint.');
					});
				}
			}, 250);
		}
	}

	$(window).on('load', function () {
		// Mailchimp OAuth connection.
		$('#mailchimp_sf_oauth_connect').click(function () {
			$('.mailchimp-sf-oauth-section .oauth-error').html('');
			$.post(
				params.ajax_url,
				{
					action: 'mailchimp_sf_oauth_start',
					nonce: params.oauth_start_nonce,
				},
				function (response) {
					if (response.success && response.data && response.data.token) {
						// Open Mailchimp OAuth popup.
						openMailChimpOauthPopup(response.data.token);
					} else {
						// eslint-disable-next-line no-console
						console.error(response.data);
						if (response.data && response.data.message) {
							$('.mailchimp-sf-oauth-section .oauth-error').html(
								response.data.message,
							);
						} else {
							$('.mailchimp-sf-oauth-section .oauth-error').html(
								'An error occurred. Please try again.',
							);
						}
					}
				},
			).fail(function () {
				$('.mailchimp-sf-oauth-section .oauth-error').html(
					'An error occurred. Please try again.',
				);
			});
		});
	});
})(jQuery);
