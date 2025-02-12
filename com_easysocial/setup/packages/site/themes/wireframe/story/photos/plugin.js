EasySocial.require()
.script("site/story/photos")
.done(function($){

	var plugin =
		story.addPlugin("photos", {
			uploader: {
				settings: {
					url: "<?php echo FRoute::raw( 'index.php?option=com_easysocial&controller=photos&task=uploadStory&uid=' . $uid . '&type=' . $type . '&format=json&tmpl=component&' . ES::token() . '=1' ); ?>",
					max_file_size: "<?php echo $maxFileSize; ?>",
					camera: "image"
				}
			},
			"errors": {
				"-601": "<?php echo JText::_('COM_EASYSOCIAL_INVALID_FILE_UPLOADED', true);?>",
				"-600": "<?php echo JText::_('COM_EASYSOCIAL_FILE_SIZE_ERROR', true);?>",
				"noEmptyAllowed": "<?php echo JText::_('COM_ES_STORY_PHOTO_NOTE_MESSAGE');?>"
			},
			uid: "<?php echo $uid; ?>",
			utype: "<?php echo $type; ?>"
		});

});
