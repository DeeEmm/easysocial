
EasySocial.require()
.library('dialog')
.done(function($) {

	window.selectAdvertiser = function(obj)
	{
		$('[data-jfield-advertiser-title]').val(obj.title);
		$('[data-jfield-advertiser-value]').val(obj.id);

		// Close the dialog when done
		EasySocial.dialog().close();
	}

	$('[data-jfield-advertiser]').on('click', function() {
		EasySocial.dialog({
			content : EasySocial.ajax('admin/views/ads/browse', {'jscallback': 'selectAdvertiser'})
		});
	});

});
