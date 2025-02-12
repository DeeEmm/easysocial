EasySocial.require()
.done(function($) {
	$('[data-es-stream-permission-member]').on('change', function() {
		var button = $(this);

		if (button.is(':checked')) {
			$('[data-es-stream-permission-profile]').show();
		} else {
			$('[data-es-stream-permission-profile]').hide();
		}
	});

	$('[data-member-type]').on('change', function() {
		var value = $(this).val();

		if (value == 'selected') {
			$('[data-es-stream-profile-type]').removeClass('t-hidden');
		} else {
			$('[data-es-stream-profile-type]').addClass('t-hidden');
		}
	});
})
