EasySocial.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'publish') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/stream/confirmPublish'),
				bindings: {
					"{publishButton} click": function() {
						$.Joomla('submitform', [task]);
					}
				}
			});
			return false;
		} else if (task == 'purge') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/stream/confirmDelete'),
				bindings: {
					"{deleteButton} click" : function() {
						$.Joomla('submitform', [task]);
					}
				}
			});
			return false;
		}

		$.Joomla('submitform', [task]);
	});
});