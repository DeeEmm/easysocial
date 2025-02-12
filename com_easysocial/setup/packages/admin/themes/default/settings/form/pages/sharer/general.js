EasySocial.ready(function($) {

	$('[data-es-embed-style]').on('change', function() {
		var value = $(this).val();
		var iconSettings = $('[data-es-embed-icon]');
		var textSettings = $('[data-es-embed-text]');

		if (value == 'full') {
			iconSettings.removeClass('t-hidden');
			textSettings.removeClass('t-hidden');
			return;
		}

		if (value == 'icon') {
			iconSettings.removeClass('t-hidden');
			textSettings.addClass('t-hidden');
			return;
		}

		if (value == 'text') {
			iconSettings.addClass('t-hidden');
			textSettings.removeClass('t-hidden');
			return;
		}
	});

});