
EasySocial.ready(function($) {

	$('[data-archive-enable]').on('change', function() {
		var input = $(this);
		var checked = input.is(':checked');

		$('[data-archive-stream-setting]').toggleClass('t-hidden', !checked);
	});
});
