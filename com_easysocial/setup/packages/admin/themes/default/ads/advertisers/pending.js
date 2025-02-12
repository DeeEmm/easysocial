EasySocial.ready(function($){
	$.Joomla('submitbutton', function(task) {


		if (task == 'approveAdvertiser') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/dialogs/render', {'file': 'admin/ads/dialogs/advertiser.approve'}),
				bindings: {
					"{submitButton} click": function() {
						$.Joomla('submitform', [task]);
					}
				}
			});

			return false;
		}

		if (task == 'rejectAdvertiser') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/dialogs/render', {'file': 'admin/ads/dialogs/advertiser.reject'}),
				bindings: {
					"{submitButton} click": function() {
						$.Joomla('submitform', [task]);
					}
				}
			});

			return false;
		}

		$.Joomla('submitform', [task]);
	});
});
