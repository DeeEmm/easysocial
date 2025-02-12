EasySocial.require()
.script('admin/marketplaces/marketplaces')
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
				content : EasySocial.ajax('admin/views/marketplaces/rejectListing', { "ids" : selected })
			});

			return false;
		}

		if (task == 'approve') {
			EasySocial.dialog(
			{
				content : EasySocial.ajax('admin/views/marketplaces/approveListing', { "ids" : selected })
			});

			return false;
		}

		$.Joomla('submitform', [task]);
	});

	$('[data-grid-row]').implement(EasySocial.Controller.Marketplaces.Pending.Item);
});
