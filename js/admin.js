/* eslint-disable prefer-template, no-console */
(function ($) {
	const params = window.mailchimp_sf_admin_params || {};
	const spinner = '.mailchimp-sf-oauth-connect-wrapper .spinner';
	const errorSelector = '.mailchimp-sf-oauth-section .oauth-error';

	/**
	 * Open Mailchimp OAuth popup.
	 *
	 * @param {string} token - Token from the Oauth service.
	 */
	function openMailchimpOauthPopup(token) {
		const startUrl = params.oauth_url + '/auth/start/' + token;
		const width = 800;
		const height = 600;
		const screenSizes = window.screen || { width: 1024, height: 768 };
		const left = (screenSizes.width - width) / 2;
		const top = (screenSizes.height - height) / 4;
		const windowOptions =
			'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' +
			width +
			', height=' +
			height +
			', top=' +
			top +
			', left=' +
			left +
			', domain=' +
			params.oauth_url.replace('https://', '');

		// Open Mailchimp OAuth popup.
		const popup = window.open(startUrl, params.oauth_window_name, windowOptions);

		if (popup == null) {
			// Show modal if popup is blocked.
			$('#login-popup-blocked-modal').dialog({
				modal: true,
				title: params.modal_title,
				width: 480,
				buttons: [
					{
						text: params.modal_button_cancel,
						class: 'button-secondary',
						click() {
							$(this).dialog('close');
						},
					},
					{
						text: params.modal_button_try_again,
						class: 'button-primary',
						click() {
							$(this).dialog('close');
							$(spinner).addClass('is-active');
							openMailchimpOauthPopup(token);
						},
						style: 'margin-left: 10px;',
					},
				],
			});
			$(spinner).removeClass('is-active');
		} else {
			// Handle popup opened.
			const oauthInterval = window.setInterval(function () {
				if (popup.closed) {
					// Clear interval.
					window.clearInterval(oauthInterval);

					// Check status of OAuth connection.
					const statusUrl = params.oauth_url + '/api/status/' + token;
					$.post(statusUrl, function (statusData) {
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
									if (finishResponse.data && finishResponse.data.message) {
										$(errorSelector).html(finishResponse.data.message);
									} else {
										$(errorSelector).html(params.generic_error);
									}
									$(errorSelector).show();
								}
								$(spinner).removeClass('is-active');
							}).fail(function () {
								console.error('Error calling OAuth finish endpoint.');
								$(errorSelector).html(params.generic_error);
								$(errorSelector).show();
								$(spinner).removeClass('is-active');
							});
						} else {
							console.log(
								'Error calling OAuth status endpoint. No credentials provided at login popup? Data:',
								statusData,
							);
							$(spinner).removeClass('is-active');
						}
					}).fail(function () {
						$(errorSelector).html(params.generic_error);
						$(errorSelector).show();
						console.error('Error calling OAuth status endpoint.');
						$(spinner).removeClass('is-active');
					});
				}
			}, 250);
		}
	}

	$(window).on('load', function () {
		// Mailchimp OAuth connection.
		$('#mailchimp_sf_oauth_connect').click(function () {
			$(errorSelector).hide();
			$(errorSelector).html('');
			$(spinner).addClass('is-active');

			$.post(
				params.ajax_url,
				{
					action: 'mailchimp_sf_oauth_start',
					nonce: params.oauth_start_nonce,
				},
				function (response) {
					if (response.success && response.data && response.data.token) {
						// Open Mailchimp OAuth popup.
						openMailchimpOauthPopup(response.data.token);
					} else {
						if (response.data && response.data.message) {
							$(errorSelector).html(response.data.message);
						} else {
							$(errorSelector).html(params.generic_error);
						}
						$(errorSelector).show();
						$(spinner).removeClass('is-active');
					}
				},
			).fail(function () {
				$(errorSelector).html(params.generic_error);
				$(errorSelector).show();
				$(spinner).removeClass('is-active');
			});
		});
	});
	// eslint-disable-next-line no-undef
})(jQuery);
