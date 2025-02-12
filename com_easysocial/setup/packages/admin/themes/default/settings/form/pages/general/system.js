
EasySocial.ready(function($) {

	$('[data-secure-cron]').on('change', function() {
		var input = $(this);
		var checked = input.is(':checked');

		$('[data-secure-cron-settings]').toggleClass('t-hidden', !checked);
	});
});