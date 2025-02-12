
EasySocial.ready(function($) {

	var successMessage = $('[data-gif-success-message]');
	var errorMessage = $('[data-gif-error-message]');
	var messageWrapper = $('[data-gif-message]');

	$('[data-toggle-gif]').on('change', function() {
		var input = $(this);
		var checked = input.is(':checked');

		// api key validation
		if (checked) {
			successMessage.addClass('t-hidden');
			errorMessage.addClass('t-hidden');

			messageWrapper.addClass('is-loading');

			EasySocial.ajax('admin/controllers/settings/validateApiKey').done(function(result) {
				
				if (result) {
					successMessage.removeClass('t-hidden');
				} else {
					input.removeAttr('checked');
					input.trigger('change');
					errorMessage.removeClass('t-hidden');
				}
			}).always(function() {
				messageWrapper.removeClass('is-loading');
			})
		}
	});
});