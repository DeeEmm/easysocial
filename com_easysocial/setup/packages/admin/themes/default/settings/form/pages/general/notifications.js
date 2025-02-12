EasySocial.ready(function($) {

	$('[data-toggle-polling-single]').on('change', function() {
		var input = $(this);
		var checked = input.is(':checked');


		$('[data-polling-interval]').toggleClass('t-hidden', checked);
	});

});
