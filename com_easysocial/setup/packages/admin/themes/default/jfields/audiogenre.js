EasySocial.require()
.library('dialog')
.done(function($) {

	var fieldTitle = $('[data-jfield-audiogenre-title]');
	var fieldValue = $('[data-jfield-audiogenre-value]');
	var browseButton = $('[data-jfield-audiogenre]');
	var removeButton = $('[data-jfield-audiogenre-remove]');

	window.selectGenre  = function(obj) {
		$('[data-jfield-audiogenre-title]').val(obj.title);

		$('[data-jfield-audiogenre-value]').val(obj.id);

		EasySocial.dialog().close();
	}

	browseButton.on('click', function() {
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/audiogenres/browse', {
				'jscallback': 'selectGenre'
			})
		});
	});

	removeButton.on('click', function() {
		fieldTitle.val('');
		fieldValue.val('');
	});

});
