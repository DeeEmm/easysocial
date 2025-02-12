EasySocial.require()
.library('dialog')
.done(function($) {

	var fieldTitle = $('[data-jfield-audio-title]');
	var fieldValue = $('[data-jfield-audio-value]');
	var browseButton = $('[data-jfield-audio]');
	var removeButton = $('[data-jfield-audio-remove]');

	window.selectAudio = function(obj) {
		$('[data-jfield-audio-title]').val(obj.title);

        $('[data-jfield-audio-value]').val(obj.id + ':' + obj.alias);

		EasySocial.dialog().close();
	}

	browseButton.on('click', function() {
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/audios/browse', {
				'jscallback': 'selectAudio'
			})
		});
	});

	removeButton.on('click', function() {
		fieldTitle.val('');
		fieldValue.val('');
	});

});
