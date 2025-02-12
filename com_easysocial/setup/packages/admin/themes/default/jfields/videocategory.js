EasySocial.require()
.library('dialog')
.done(function($) {

	var fieldTitle = $('[data-jfield-videocategory-title]');
	var fieldValue = $('[data-jfield-videocategory-value]');
	var browseButton = $('[data-jfield-videocategory]');
	var removeButton = $('[data-jfield-videocategory-remove]');

	window.selectCategory  = function(obj) {
		$('[data-jfield-videocategory-title]').val(obj.title);

		$('[data-jfield-videocategory-value]').val(obj.id);

		EasySocial.dialog().close();
	}

	browseButton.on('click', function() {
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/videocategories/browse', {
				'jscallback': 'selectCategory'
			})
		});
	});

	removeButton.on('click', function() {
		fieldTitle.val('');
		fieldValue.val('');
	});

});
