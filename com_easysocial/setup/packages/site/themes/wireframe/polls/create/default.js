EasySocial.require()
.done(function($){
	// Custom actions when a friend request is rejected
	$(document).on('click', '[data-polls-submit-btn]', function() {
		var el = $(this);
		var formWrapper = el.closest('[data-polls-wrappr-form]');

		// Disable the button
		el.disabled(true);

		var pollController = formWrapper.find('[data-polls-form]').controller('EasySocial.Controller.Polls.Form');
		var alertMsg = formWrapper.find('[data-polls-alert]');

		// hide alert message after user click on submit button.
		alertMsg.addClass('t-hidden');

		if (pollController !== undefined) {
			var valid = pollController.validateForm();

			if (!valid) {
				//show error message.
				alertMsg.removeClass('t-hidden');

				// Enable back the button
				el.enabled(true);

				return false;
			}
		}

		// submit form.
		formWrapper.submit();
	});
});