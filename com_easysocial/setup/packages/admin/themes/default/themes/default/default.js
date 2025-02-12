
EasySocial.ready(function($) {

	$.Joomla('submitbutton', function(task) {
		
		if (task == 'edit') {
			var element = $('input[name=cid\\[\\]]:checked').val() || '';

			if (!element) {
				return;
			}
			
			window.location = '<?php echo JURI::base();?>index.php?option=com_easysocial&view=themes&layout=edit&element=' + element;
			return;
		}

		$.Joomla('submitform', [task]);
	});
});
