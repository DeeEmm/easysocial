EasySocial.ready(function($) {

	$.Joomla('submitbutton', function(task) {
		if (task == 'cancel') {
			window.location.href = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=badges';
			return false;
		}

		$.Joomla('submitform', [task]);
	});

	// Toggle options between achieving type
	$('[data-es-badges-achieve-type]').on('change', function() {
		var type = $(this).val();
		
		// toggle points form
		if (type == 'points') {
			$('[data-es-badges-points]').removeClass('hidden');
			$('[data-es-badges-frequency]').addClass('hidden');
		} else {
			$('[data-es-badges-points]').addClass('hidden');
			$('[data-es-badges-frequency]').removeClass('hidden');
		}
	})
});