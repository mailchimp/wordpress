/* Form submission functions for the Mailchimp Widget */
(function ($) {
	function mc_beforeForm() {
		// Disable the submit button
		$('.mc_signup_submit_button').attr('disabled', 'disabled');
	}

	function mc_success(data, status, xhr, submittedForm) {
		let form = $(submittedForm);
		if (!submittedForm || !form) {
			form = $('.mc_signup_form');
		}

		// Re-enable the submit button
		$('.mc_signup_submit_button').removeAttr('disabled');

		// Put the response in the message div
		form.find('.mc_message_wrapper').html(data);

		// See if we're successful, if so, wipe the fields
		const reg = /class=(["'])mc_success_msg\1/i;

		if (reg.test(data)) {
			$(form).each(function () {
				this.reset();
			});

			$('.mc_submit_type').val('js');
		}

		window.scrollTo({
			top: parseInt(form.offset().top, 10) - 28,
			behavior: 'smooth',
		});
	}

	$(function ($) {
		// Change our submit type from HTML (default) to JS
		$('.mc_submit_type').val('js');

		// Remove the no JS field.
		$('.mailchimp_sf_no_js').remove();

		// Attach our form submitter action
		$('.mc_signup_form').ajaxForm({
			url: window.mailchimpSF.ajax_url,
			type: 'POST',
			dataType: 'text',
			beforeSubmit: mc_beforeForm,
			success: mc_success,
		});
	});
})(window.jQuery);

/* Datepicker functions for the Mailchimp Widget */
(function ($) {
	if ($('.date-pick').length > 0) {
		// Datepicker for the date-pick class
		$('.date-pick').each(function () {
			let format = $(this).data('format') || 'mm/dd/yyyy';
			format = format.replace(/yyyy/i, 'yy');
			$(this).datepicker({
				autoFocusNextInput: true,
				constrainInput: false,
				changeMonth: true,
				changeYear: true,
				// eslint-disable-next-line no-unused-vars
				beforeShow(input, inst) {
					$('#ui-datepicker-div').addClass('show');
				},
				dateFormat: format.toLowerCase(),
			});
		});
	}

	if ($('.birthdate-pick').length > 0) {
		const d = new Date();
		$('.birthdate-pick').each(function () {
			let format = $(this).data('format') || 'mm/dd';
			format = format.replace(/yyyy/i, 'yy');
			$(this).datepicker({
				autoFocusNextInput: true,
				constrainInput: false,
				changeMonth: true,
				changeYear: false,
				minDate: new Date(d.getFullYear(), 1 - 1, 1),
				maxDate: new Date(d.getFullYear(), 12 - 1, 31),
				// eslint-disable-next-line no-unused-vars
				beforeShow(input, inst) {
					$('#ui-datepicker-div').removeClass('show');
				},
				dateFormat: format.toLowerCase(),
			});
		});
	}

	// Phone validation custom error message.
	if ($('.mailchimp-sf-phone').length > 0) {
		$('.mailchimp-sf-phone').each(function () {
			$(this)
				.on('input', function () {
					this.setCustomValidity('');
				})
				.on('invalid', function () {
					if (!this.validity.valid) {
						this.setCustomValidity(window.mailchimpSF.phone_validation_error);
					}
				});
		});
	}
})(window.jQuery);
