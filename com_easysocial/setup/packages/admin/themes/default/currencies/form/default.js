EasySocial.ready(function($) {

	$.Joomla('submitbutton', function(task) {
		if (task == 'cancel') {
			window.location.href = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=currencies';
			return false;
		}

		$('[data-id]').removeAttr('disabled');

		$.Joomla('submitform', [task]);
	});
});
