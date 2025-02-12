
EasySocial.ready(function($) {

	$('[data-es-alert-item]').on('click', function() {

		var current = $(this);

		// Remove all active states
		$('[data-es-alert-item]').removeClass('active');

		// Add active state on the current alert type
		current.addClass('active');

		$('[data-es-alert-contents]').removeClass('is-active');

		var type = current.data('type');
		var selector = '[data-es-alert-contents="' + type + '"]';

		$(selector).addClass('is-active');

		$('[data-alert-active]').val(type);
	});
});
