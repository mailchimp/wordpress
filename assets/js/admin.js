/* eslint-disable prefer-template, no-console */
(function ($) {
	const params = window.mailchimp_sf_admin_params || {};
	const spinner = '#mailchimp_sf_oauth_connect .mailchimp-sf-loading';
	const errorSelector = '.mailchimp-sf-oauth-error';

	/**
	 * Set connect button loading state.
	 */
	function setConnectButtonLoading() {
		$(spinner).removeClass('hidden');
		$('#mailchimp_sf_oauth_connect').attr('disabled', true);
	}

	/**
	 * Set connect button normal state.
	 */
	function setConnectButtonNormal() {
		$(spinner).addClass('hidden');
		$('#mailchimp_sf_oauth_connect').attr('disabled', false);
	}

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
			$('#mailchimp-sf-popup-blocked-modal').dialog({
				modal: true,
				title: params.modal_title,
				width: 480,
				buttons: [
					{
						text: params.modal_button_cancel,
						class: 'button mailchimp-sf-button button-secondary',
						click() {
							$(this).dialog('close');
						},
					},
					{
						text: params.modal_button_try_again,
						class: 'button mailchimp-sf-button',
						click() {
							$(this).dialog('close');
							setConnectButtonLoading();
							openMailchimpOauthPopup(token);
						},
						style: 'margin-left: 10px;',
					},
				],
				classes: {
					'ui-dialog': 'mailchimp-sf-ui-dialog',
					'ui-dialog-titlebar': 'mailchimp-sf-ui-dialog-titlebar',
				},
			});
			setConnectButtonNormal();
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
									// Token is saved in the database, redirect to the settings page to reflect the changes.
									window.location.href = params.admin_settings_url;
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
								setConnectButtonNormal();
							}).fail(function () {
								console.error('Error calling OAuth finish endpoint.');
								$(errorSelector).html(params.generic_error);
								$(errorSelector).show();
								setConnectButtonNormal();
							});
						} else {
							console.log(
								'Error calling OAuth status endpoint. No credentials provided at login popup? Data:',
								statusData,
							);
							setConnectButtonNormal();
						}
					}).fail(function () {
						$(errorSelector).html(params.generic_error);
						$(errorSelector).show();
						console.error('Error calling OAuth status endpoint.');
						setConnectButtonNormal();
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
			setConnectButtonLoading();

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
						setConnectButtonNormal();
					}
				},
			).fail(function () {
				$(errorSelector).html(params.generic_error);
				$(errorSelector).show();
				setConnectButtonNormal();
			});
		});
	});

	/**
	 * Create Mailchimp account Handler.
	 */
	// Waiting for login.
	const waitingForMailchimpAccountLogin = () => {
		const intervalId = window.setInterval(function () {
			$.post(
				params.ajax_url,
				{
					action: 'mailchimp_sf_check_login_session',
					nonce: params.check_login_session_nonce,
				},
				function (response) {
					if (response.success && response.data && response.data.logged_in) {
						window.clearInterval(intervalId);
						window.location.href = response.data.redirect;
					} else {
						console.log(response);
					}
				},
			);
		}, 10000);
	};

	$(window).on('load', function () {
		const isCreateAccountPage = $('.mailchimp-sf-create-account').length > 0;
		if (!isCreateAccountPage) {
			return;
		}

		// Check if signup initiated.
		if ($('.mailchimp-sf-create-account input[name=signup_initiated]').val() === '1') {
			waitingForMailchimpAccountLogin();
		}

		// Validate inputs.
		const validateFormInput = (input) => {
			let inputLabel = '';
			if (
				$('label[for="' + input.id + '"] span').length > 0 &&
				$('label[for="' + input.id + '"] span').text()
			) {
				inputLabel = $('label[for="' + input.id + '"] span')
					.text()
					.trim();
				inputLabel = inputLabel.split('/')[0];
				inputLabel = inputLabel.split('(')[0];
			}
			const requiredError = (params.required_error || '').replace('%s', inputLabel);
			const requiredInputs = [
				'first_name',
				'last_name',
				'business_name',
				'email',
				'address',
				'country',
				'city',
				'state',
				'zip',
			];
			if (requiredInputs.includes(input.name) && input.value === '') {
				return requiredError;
			}

			if (input.name === 'email') {
				if (!input.value.includes('@') || !input.value.includes('.'))
					return params.invalid_email_error;
				if (input.value !== $('#mailchimp-sf-profile-details input#confirm_email').val())
					return params.confirm_email_match;
			}
			if (input.name === 'confirm_email') {
				if (input.value !== $('#mailchimp-sf-profile-details input#email').val())
					return params.confirm_email_match2;
			}

			return null;
		};

		// Display errors and disable button in case of errors
		const validateAccountForm = (errors, wrapperId, displayErrors = false) => {
			const inputIds = Object.keys(errors);

			inputIds.forEach((key) => {
				const inputElementId = `${wrapperId} #${key}`;
				const errorElementId = `${wrapperId} #mailchimp-sf-${key}-error`;

				if (errors[key] !== null) {
					if (displayErrors) {
						$(inputElementId).closest('.box').addClass('form-error');
						$(errorElementId).text(errors[key]);
					}
				} else {
					$(inputElementId).closest('.box').removeClass('form-error');
					$(errorElementId).text('');
				}
			});
			return Object.values(errors).filter((error) => error !== null).length === 0;
		};

		// Get form Errors.
		const getFormErrors = (inputs) => {
			const errors = {};
			inputs.each((index, input) => {
				errors[input.name] = validateFormInput(input);
			});

			return errors;
		};

		// Validate profile details
		let profileDetailsInputs = $('#mailchimp-sf-profile-details input');
		profileDetailsInputs.on('input', (e) => {
			const input = e.target;

			$(input).closest('.box').removeClass('form-error');
			$(input).closest('.box').find('.error-field').text('');

			if (input.name === 'email' || input.name === 'confirm_email') {
				$('input#confirm_email, input#email').closest('.box').removeClass('form-error');
				$('input#confirm_email, input#email').closest('.box').find('.error-field').text('');
			}
		});

		// validate business address
		let businessAddressInputs = $(
			'#mailchimp-sf-business-address input, #mailchimp-sf-business-address select',
		);
		businessAddressInputs.on('input', (e) => {
			const input = e.target;

			$(input).closest('.box').removeClass('form-error');
			$(input).closest('.box').find('.error-field').text('');
		});

		// Handle create account button click.
		$('#mailchimp-sf-create-activate-account').click((e) => {
			e.preventDefault();

			profileDetailsInputs = $('#mailchimp-sf-profile-details input');
			const profileErrors = getFormErrors(profileDetailsInputs);
			const profileDetailsValid = validateAccountForm(
				profileErrors,
				'#mailchimp-sf-profile-details',
				true,
			);

			businessAddressInputs = $(
				'#mailchimp-sf-business-address input, #mailchimp-sf-business-address select',
			);
			const businessAddressErrors = getFormErrors(businessAddressInputs);
			const businessAddressValid = validateAccountForm(
				businessAddressErrors,
				'#mailchimp-sf-business-address',
				true,
			);

			if (profileDetailsValid && businessAddressValid) {
				$('.mailchimp-sf-activate-account').submit();
			}
		});

		$('.mailchimp-sf-activate-account').submit((e) => {
			e.preventDefault();
			$('#mailchimp-sf-create-activate-account').attr('disabled', true);
			$('#mailchimp-sf-create-activate-account .mailchimp-sf-loading').removeClass('hidden');

			const errorSelector = '.mailchimp-sf-create-account .general-error p';
			$(errorSelector).html('');
			const formData = $(e.target).serializeArray();
			const formDataObject = {};
			formData.forEach((obj) => {
				formDataObject[obj.name] = obj.value;
			});

			const postData = {
				email: formDataObject.email,
				username: formDataObject.email,
				business_name: formDataObject.business_name,
				first_name: formDataObject.first_name,
				last_name: formDataObject.last_name,
				org: formDataObject.org,
				phone_number: formDataObject.phone_number,
				timezone: formDataObject.timezone,
				address: {
					address1: formDataObject.address,
					city: formDataObject.city,
					state: formDataObject.state,
					zip: formDataObject.zip,
					country: formDataObject.country,
				},
			};

			// Add address2 if available.
			if (formDataObject.address2 !== '') {
				postData.address.address2 = formDataObject.address2;
			}

			$.post(
				params.ajax_url,
				{
					action: 'mailchimp_sf_create_account',
					data: postData,
					nonce: params.create_account_nonce,
				},
				function (response) {
					$('.mailchimp-sf-email').text(formDataObject.email);
					$('#mailchimp-sf-create-activate-account').attr('disabled', false);
					$('#mailchimp-sf-create-activate-account .mailchimp-sf-loading').addClass(
						'hidden',
					);

					if (response.success && response.data) {
						$('.mailchimp-sf-create-account__body-inner').addClass('hidden');
						$('.mailchimp-sf-confirm-email-wrapper').removeClass('hidden');

						// Update wizard steps.
						$('.wizard-steps .step-1').removeClass('current');
						$('.wizard-steps .step-2').removeClass('deselected');
						$('.wizard-steps .step-2').addClass('current');

						// Waiting for login.
						waitingForMailchimpAccountLogin();
					} else if (response.data && response.data.suggest_login) {
						$('.mailchimp-sf-create-account__body-inner').addClass('hidden');
						$('.mailchimp-sf-suggest-to-login').removeClass('hidden');
					} else if (response.data && response.data.message) {
						$(errorSelector).html(response.data.message);
						window.scrollTo({ top: 0, behavior: 'smooth' });
					} else {
						$(errorSelector).html(params.generic_error);
						window.scrollTo({ top: 0, behavior: 'smooth' });
					}
				},
			).fail(function () {
				$(errorSelector).html(params.generic_error);
				window.scrollTo({ top: 0, behavior: 'smooth' });
				$('#mailchimp-sf-create-activate-account').attr('disabled', false);
				$('#mailchimp-sf-create-activate-account .mailchimp-sf-loading').addClass('hidden');
			});
		});
	});
})(jQuery); // eslint-disable-line no-undef
