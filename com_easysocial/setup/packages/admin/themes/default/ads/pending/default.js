EasySocial.ready(function($){
	$.Joomla('submitbutton', function(task) {

		if (task == 'approve') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/dialogs/render', {'file': 'admin/ads/dialogs/ads.approve'}),
				bindings: {
					"{submitButton} click": function() {
						$.Joomla('submitform', [task]);
					}
				}
			});

			return false;
		}

		if (task == 'reject') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/dialogs/render', {'file': 'admin/ads/dialogs/ads.reject'}),
				bindings: {
					"{submitButton} click": function() {
						var deleteAd = this.deleteAd().is(':checked');
						var message = this.message().val();

						if (deleteAd) {
							$('<input type="hidden" name="deleteAd" value="1" />').appendTo('[data-table-grid]');
						}

						$('<input type="hidden" name="message" value="' + message + '" />').appendTo('[data-table-grid]');

						$.Joomla('submitform', [task]);
					}
				}
			});

			return false;
		}

		$.Joomla('submitform', [task]);
	});
});
