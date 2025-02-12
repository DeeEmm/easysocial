EasySocial.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'add') {
			window.location = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=stream&layout=backgroundForm';
			return;
		}

		$.Joomla('submitform', [task]);
	});
});