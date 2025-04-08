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
