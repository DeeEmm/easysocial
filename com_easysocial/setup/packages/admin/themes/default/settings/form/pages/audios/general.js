EasySocial.ready(function($) {

	$('[data-audio-uploads]').on('change', function() {
		var checkbox = $(this);
		var checked = checkbox.is(':checked');

		$('[data-encoder-option]').toggleClass('t-hidden', !checked);

		var echecked = $('[data-enable-encoder]').is(':checked');
		var showOptions = (echecked && checked);
		
		$('[data-audio-encoding]').toggleClass('t-hidden', !showOptions);
	});

	$('[data-audio-embed]').on('change', function() {
		var checkbox = $(this);
		var checked = checkbox.is(':checked');

		$('[data-embed-spotify]').toggleClass('t-hidden', !checked);
		$('[data-embed-soundcloud]').toggleClass('t-hidden', !checked);
	});

	$('[data-enable-encoder]').on('change', function() {
		var checkbox = $(this);
		var checked = checkbox.is(':checked');

		$('[data-audio-encoding]').toggleClass('t-hidden', !checked);
	});
});