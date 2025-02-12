EasySocial.ready(function($) {


	// Handle submit button.
	$.Joomla('submitbutton' , function(action) {
		if (action == 'purgeAll') {
			EasySocial.dialog({
				"content": EasySocial.ajax('admin/views/users/confirmPurgeDownloads'),
				"bindings": {
					"{purgeButton} click" : function() {
						Joomla.submitform(['purgeDownloads']);
					}
				}
			});

			return false;
		}

		$.Joomla('submitform', [action]);
	});
});