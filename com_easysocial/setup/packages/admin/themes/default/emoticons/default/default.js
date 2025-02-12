EasySocial.ready(function($){
	$.Joomla('submitbutton', function(task) {

		if (task == 'add') {
			window.location = '<?php echo JURI::base();?>index.php?option=com_easysocial&view=emoticons&layout=form';
			return false;
		}

		if (task == 'remove') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/emoticons/confirmDelete'),
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
