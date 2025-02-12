EasySocial.ready(function($) {

	$('[data-player-logo]').on('change', function() {
		var enabled = $(this).is(':checked');

		if (enabled) {
			$('[data-logo-form]').removeClass('t-hidden');
			return;	
		}

		$('[data-logo-form]').addClass('t-hidden');
	});

	$('[data-player-watermark]').on('change', function() {
		var enabled = $(this).is(':checked');

		if (enabled) {
			$('[data-watermark-form]').removeClass('t-hidden');
			return;	
		}

		$('[data-watermark-form]').addClass('t-hidden');
	});
});