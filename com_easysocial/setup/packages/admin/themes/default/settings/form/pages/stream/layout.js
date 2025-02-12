EasySocial.ready(function($) {

	$('[data-timestamp-style]').on('change', function() {
		var input = $(this);
		var value = input.val();

		$('[data-datetime-format]').toggleClass('t-hidden', value != 'datetime');
	});
});