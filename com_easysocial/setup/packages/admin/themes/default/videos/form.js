EasySocial.require()
.script('site/videos/form')
.done(function($) {

	$('[data-videos-form]').implement(EasySocial.Controller.Videos.Form);

	$.Joomla('submitbutton', function(task) {

		if (task == 'cancel') {
			window.location = "<?php echo JURI::base();?>index.php?option=com_easysocial&view=videos";

			return;
		}

		$.Joomla('submitform', [task]);
	});
});
