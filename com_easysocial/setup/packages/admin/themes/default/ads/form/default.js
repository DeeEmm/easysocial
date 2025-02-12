EasySocial.ready(function($) {

	$.Joomla('submitbutton', function(task) {
		if (task == 'cancel') {
			window.location.href = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=ads';
			return false;
		}

		if (task == 'approve') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/dialogs/render', {'file': 'admin/ads/dialogs/ads.approve'}),
				bindings: {
					"{submitButton} click": function() {
						$.Joomla('submitform', ['save']);
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
							$('<input type="hidden" name="deleteAd" value="1" />').appendTo('[data-ad-form]');
						}

						$('<input type="hidden" name="message" value="' + message + '" />').appendTo('[data-ad-form]');

						$.Joomla('submitform', [task]);
					}
				}
			});

			return false;
		}

		$.Joomla('submitform', [task]);
	});

	$('[data-time-limit]').on('change', function() {
		var input = $(this);
		var checked = input.is(':checked');

		$('[data-start-date]').toggleClass('t-hidden', !checked);
		$('[data-end-date]').toggleClass('t-hidden', !checked);
	});

});
