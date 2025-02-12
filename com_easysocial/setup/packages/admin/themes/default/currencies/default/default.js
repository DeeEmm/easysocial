EasySocial.ready(function($){
	$.Joomla('submitbutton', function(task) {

		if (task == 'add') {
			window.location = '<?php echo JURI::base();?>index.php?option=com_easysocial&view=currencies&layout=form';
			return false;
		}

		if (task == 'remove') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/dialogs/render', {"file": "admin/currencies/dialogs/delete"}),
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
