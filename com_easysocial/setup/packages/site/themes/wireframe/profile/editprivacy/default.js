
EasySocial.require()
.script('site/profile/privacy')
.done(function($){
	$('[data-edit-privacy]').implement(EasySocial.Controller.Profile.Privacy);

	$('[data-es-privacy-item]').on('click', function() {

		var current = $(this);
		var type = current.data('type');

		// Remove all active states
		$('[data-es-privacy-item]').removeClass('active');

		current.addClass('active');

		$('[data-contents]').hide();
		$('[data-contents]').filter('[data-type=' + type + ']').show();

		if (type == 'blocked') {
			$('[data-form-actions]').hide();
		} else {
			$('[data-form-actions]').show();
		}

		$('[data-privacy-active]').val(type);
	});
});
