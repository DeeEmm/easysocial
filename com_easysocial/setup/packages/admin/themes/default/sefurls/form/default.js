EasySocial.require()
.script('admin/api/toolbar')
.done(function($){

	$.Joomla('submitbutton', function(task) {

		if (task == 'cancel') {
			window.location = 'index.php?option=com_easysocial&view=sefurls';
			return false;
		}

		$.Joomla('submitform', [task]);
	});
});
