EasySocial.ready(function($) {

	// Bind the insert emoji button
	$(document).on('click.profile.insert.emoji', '[data-insert-emoji]', function() {
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/emoticons/browseEmojis'),
			bindings: {
				"{addEmoji} click" : function(e) {
					var emoji = e.data('emoji');

					$('[data-emoji-input]').val(emoji);
					$('[data-preview-emoji]').html(emoji);
					$('[data-emoji-empty]').addClass('t-hidden');
					
					EasySocial.dialog().close();
				}
			}
		});
	});

	$('[data-emoticon-type]').change(function(){
		showEmoji = $(this).val() == 'unicode';

		$('[data-emoji-browser]').toggleClass('t-hidden', !showEmoji);
		$('[data-image-uploader]').toggleClass('t-hidden', showEmoji);
	});

	$.Joomla('submitbutton', function(task) {
		if (task == 'cancel') {
			window.location.href = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=emoticons';
			return false;
		}

		$.Joomla('submitform', [task]);
	});
});