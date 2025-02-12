
EasySocial.ready(function($) {

	$('[data-mailer-list]').implement(EasySocial.Controller.Mailer)

	// Handle submit button.
	$.Joomla('submitbutton' , function(action) {
		var selected = [];

		if (action == 'reset') {

			$('[data-table-grid]').find('input[name=cid\\[\\]]:checked').each(function(i , el ) {
				selected.push($(el).val());
			});

			EasySocial.dialog({
				"content": EasySocial.ajax('admin/views/mailer/confirmReset', {"files": selected})
			});

			return false;
		}

		$.Joomla('submitform', [action]);
	});

});
