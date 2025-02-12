EasySocial.require()
.script('site/audios/form')
.done(function($) {

	$('[data-audios-form]').implement(EasySocial.Controller.Audios.Form);

	$.Joomla('submitbutton', function(task) {

		if (task == 'cancel') {
			window.location = "<?php echo JURI::base();?>index.php?option=com_easysocial&view=audios";

			return;
		}

		$.Joomla('submitform', [task]);
	});
});