
EasySocial.require()
.library('dialog')
.done(function($) {

	window.selectPage = function(obj) {
		$('[data-jfield-page-title]').val(obj.title);

		$('[data-jfield-page-value]').val(obj.id + ':' + obj.alias);

		// Close the dialog when done
		EasySocial.dialog().close();
	}

	$('[data-jfield-page]').on('click', function()
	{
		EasySocial.dialog(
		{
			content : EasySocial.ajax('admin/views/pages/browse' , { 'jscallback' : 'selectPage' })
		});
	});

});
