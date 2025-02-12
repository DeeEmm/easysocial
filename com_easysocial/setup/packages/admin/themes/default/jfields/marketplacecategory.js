
EasySocial.require()
.library('dialog')
.done(function($) {
	window.selectMarketplaceCategory = function(obj) {
		$('[data-jfield-marketplacecategory-title]').val(obj.title);

		$('[data-jfield-marketplacecategory-value]').val(obj.id + ':' + obj.alias);

		// Close the dialog when done
		EasySocial.dialog().close();
	}

	$('[data-jfield-marketplacecategory-remove]').on('click', function() {

		// Reset the category value
		$('[data-jfield-marketplacecategory-value]').val('');
		$('[data-jfield-marketplacecategory-title]').val('');

	});

	$('[data-jfield-marketplacecategory]').on('click', function() {
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/marketplaces/browseCategory', {
				'jscallback': 'selectMarketplaceCategory'
			})
		});
	});

});
