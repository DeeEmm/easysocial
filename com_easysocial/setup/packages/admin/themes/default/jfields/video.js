EasySocial.require()
.library('dialog')
.done(function($) {
	
	var fieldTitle = $('[data-jfield-video-title]');
	var fieldValue = $('[data-jfield-video-value]');
	var browseButton = $('[data-jfield-video]');
	var removeButton = $('[data-jfield-video-remove]');

	window.selectVideo = function(obj) {
		$('[data-jfield-video-title]').val(obj.title);

		$('[data-jfield-video-value]').val(obj.id + ':' + obj.alias);

		EasySocial.dialog().close();
	}

	browseButton.on('click', function() {
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/videos/browse', {
				'jscallback': 'selectVideo'
			})
		});
	});

	removeButton.on('click', function() {
		fieldTitle.val('');
		fieldValue.val('');
	});

});
