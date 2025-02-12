EasySocial.require()
.done(function($){

	$('[data-table-grid]').on('beforeSubmitForm', function(event, task) {
		if (task == 'publish' || task == 'unpublish') {
			$('input[name=controller]').val('apps');
		}
	});

	$.Joomla('submitbutton', function(task) {

		// Route to the appropriate controller
		if (task == 'unpublish' || task == 'publish' || task == 'uninstall') {
			$('input[name=controller]').val('apps');

			$.Joomla('submitform', [task]);
			return;
		}

		if (task == 'uninstall') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/apps/confirmUninstall'),
				bindings: {
					"{proceedButton} click" : function() {
						$.Joomla( 'submitform' , [task] );
					}
				}
			});

			return false;
		}

		$.Joomla('submitform', [task]);
	});

	$('[data-app-update]').on('click', function() {
		var id = $(this).data('id');

		EasySocial.dialog({
			"content": EasySocial.ajax('admin/views/store/confirmation', {
				"id": id,
				"return": "<?php echo base64_encode('index.php?option=com_easysocial&view=workflows&layout=fields');?>"
			})
		});
	});
});
