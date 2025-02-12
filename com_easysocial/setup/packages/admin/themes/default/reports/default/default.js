EasySocial.ready(function($){

	$('[data-reports-item-view-reports]')
		.on('click', function() {
			var button = $(this);
			var item = button.parents('[data-reports-item]');
			var id = item.data('id');

			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/reports/getReporters',  {"id": id})
			});
		});

	$.Joomla('submitbutton', function(task) {
		
		if (task == 'purge') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/reports/confirmPurge'),
				bindings: {
					"{confirmButton} click": function() {
						return $.Joomla( 'submitform' , [task] );
					}
				}
			});
		}

		if (task == 'remove') {
			EasySocial.dialog( {
				"content": EasySocial.ajax('admin/views/reports/confirmDelete'),
				"bindings": {
					"{confirmButton} click" : function() {
						return $.Joomla('submitform', [task]);
					}
				}
			});
		}

		return false;

	});
});
