
EasySocial
.ready(function($) {
	$.Joomla('submitbutton', function(action) {
		
		if (action == 'cancel') {
			window.location.href = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=points';
			return;
		}

		$.Joomla('submitform', [action]);
	});
});