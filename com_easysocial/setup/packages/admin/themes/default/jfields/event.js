EasySocial.require()
.library('dialog')
.done(function($) {

	window.selectEvent = function(obj) {
		$('[data-jfield-event-title]').val(obj.title);

		$('[data-jfield-event-value]').val(obj.alias);

		EasySocial.dialog().close();
	}

	$('[data-jfield-event]').on('click', function() {
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/events/browse', {
				'jscallback': 'selectEvent',
				'multiple': 0
			})
		});
	});
});
