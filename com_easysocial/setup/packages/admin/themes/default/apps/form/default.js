EasySocial.ready(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'cancel') {
			<?php if ($app->type == 'fields') { ?>
				window.location = '<?php echo $customRedirect;?>';
			<?php } else { ?>
				window.location = '<?php echo JURI::base();?>index.php?option=com_easysocial&view=apps';
			<?php } ?>
			return;
		}

		$.Joomla('submitform', [task]);
	});
});
