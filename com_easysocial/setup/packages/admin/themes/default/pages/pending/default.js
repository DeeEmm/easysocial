EasySocial.require()
.script('admin/pages/pages')
.done(function($){

	$.Joomla('submitbutton', function(task)
	{
		var selected 	= new Array;

		$('[data-table-grid]').find('input[name=cid\\[\\]]:checked').each(function(i, el ){
			selected.push($(el).val());
		});

		if (task == 'reject') {
			EasySocial.dialog(
			{
				content : EasySocial.ajax('admin/views/pages/rejectPage', { "ids" : selected })
			});

			return false;
		}

		if (task == 'approve') {
			EasySocial.dialog(
			{
				content : EasySocial.ajax('admin/views/pages/approvePage', { "ids" : selected })
			});

			return false;
		}

		$.Joomla('submitform', [task]);
	});

	$('[data-grid-row]').implement(EasySocial.Controller.Pages.Pending.Item);
});
