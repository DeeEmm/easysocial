
EasySocial.require()
.library('dialog')
.done(function($) {

	window.selectMarketplace 	= function(obj) {
		$('[data-jfield-marketplace-title]').val(obj.title);
		$('[data-jfield-marketplace-value]').val(obj.id + ':' + obj.alias);

		// Close the dialog when done
		EasySocial.dialog().close();
	}

	$('[data-jfield-marketplace]').on('click', function() {
		EasySocial.dialog(
		{
			content : EasySocial.ajax('admin/views/marketplaces/browse' , {'jscallback' : 'selectMarketplace'})
		});
	});

	$('[data-jfield-marketplace-remove]').on('click', function() {
		// Reset the marketplace value
		$('[data-jfield-marketplace-value]').val('');
		$('[data-jfield-marketplace-title]').val('');
	});

});
