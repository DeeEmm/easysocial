
EasySocial.ready(function($){

	$.Joomla('submitbutton', function(task) {
		
		if (task == 'remove') {
			EasySocial.dialog({
				"content": EasySocial.ajax('admin/views/polls/confirmDelete'),
				"bindings": {
					"{confirmButton} click": function() {
						return $.Joomla('submitform' , [task]);
					}
				}
			});
		}

		return false;

	});
});
