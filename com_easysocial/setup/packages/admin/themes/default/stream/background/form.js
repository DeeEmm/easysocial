EasySocial.ready(function($) {

	$('[data-background-type]').on('change', function() {
		var val = $(this).val();

		if (val == 'gradient') {
			$('[data-second-color]').removeClass('t-hidden');
			return;
		}
		
		$('[data-second-color]').addClass('t-hidden');
		return;
	});

	$.Joomla('submitbutton', function(task) {

		if (task == 'cancel') {
			window.location = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=stream&layout=background';
			return;
		}

		$.Joomla('submitform', [task]);
	});
});