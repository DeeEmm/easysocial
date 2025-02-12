EasySocial.ready(function($) {

	$.Joomla('submitbutton', function(task) {
		if (task == 'cancel') {
			window.location.href = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=ads&layout=advertisers';
			return false;
		}

		if (task == 'approveAdvertiser') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/dialogs/render', {'file': 'admin/ads/dialogs/advertiser.approve'}),
				bindings: {
					"{submitButton} click": function() {
						$.Joomla('submitform', ['saveAdvertiser']);
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
