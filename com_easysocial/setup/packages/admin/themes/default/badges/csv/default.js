EasySocial.ready(function($) {
	$.Joomla('submitbutton', function(task) {
		$.Joomla('submitform', [task]);
	});
});