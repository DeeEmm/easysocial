EasySocial.ready(function($) {

	$('[data-links-auto-purge]').on('change', function() {
		var checked = $(this).is(':checked');
		var interval = $('[data-links-auto-purge-interval]');

		if (checked) {
			interval.removeClass('t-hidden');
			return;
		}

		interval.addClass('t-hidden');
	});

});