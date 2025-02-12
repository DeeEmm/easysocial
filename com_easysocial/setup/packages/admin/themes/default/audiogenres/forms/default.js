
EasySocial.require()
.script('admin/utilities/alias')
.done(function($) {

	$.Joomla('submitbutton', function(task) {

		if (task == 'cancel') {
			window.location = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=audiogenres';

			return;
		}

		return $.Joomla('submitform', [task]);
	});


	$('[data-audios-genre-form]').implement(EasySocial.Controller.Utilities.Alias, {
		"{source}": "[data-genre-title]",
		"{target}": "[data-genre-alias]"
	});
});