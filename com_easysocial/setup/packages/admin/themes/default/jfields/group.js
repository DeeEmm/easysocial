
EasySocial.require()
.library('dialog')
.done(function($) {

	window.selectGroup 	= function(obj) {
		$('[data-jfield-group-title]').val(obj.title);
		$('[data-jfield-group-value]').val(obj.id + ':' + obj.alias);

		// Close the dialog when done
		EasySocial.dialog().close();
	}

	$('[data-jfield-group]').on('click', function() {
		EasySocial.dialog(
		{
			content : EasySocial.ajax('admin/views/groups/browse' , {'jscallback' : 'selectGroup'})
		});
	});

	$('[data-jfield-group-remove]').on('click', function() {
		// Reset the group value
		$('[data-jfield-group-value]').val('');
		$('[data-jfield-group-title]').val('');
	});

});
