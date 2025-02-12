EasySocial.require()
.done(function($){

	$.Joomla('submitbutton', function(task) {

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

		if (task == 'refresh') {
			$('[data-table-grid-controllers]').val('store');
		}

		$.Joomla( 'submitform' , [task] );
	});

	$('[data-app-update]').on('click', function() {
		var id = $(this).data('id');

		EasySocial.dialog({
			"content": EasySocial.ajax('admin/views/store/confirmation', {
				"id": id,
				"return": "<?php echo base64_encode('index.php?option=com_easysocial&view=apps');?>"
			})
		});
	});
});
