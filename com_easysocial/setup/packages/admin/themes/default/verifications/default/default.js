
EasySocial
.ready(function($){

	$.Joomla('submitbutton', function(task) {
		if (task == 'approve' || task == 'reject') {
			$('[data-table-grid-controller]').val('verifications');
		}

		$.Joomla('submitform', [task]);
	});

	$('[data-verify-message]').on('click', function() {
		var id = $(this).data('id');

		EasySocial.dialog({
			content : EasySocial.ajax('admin/views/verifications/viewMessage', {
				"id" : id
			})
		})
	});
});
