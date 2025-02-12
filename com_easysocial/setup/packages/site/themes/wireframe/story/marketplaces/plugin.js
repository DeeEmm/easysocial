EasySocial.require()
.script("site/story/marketplace")
.done(function($) {
		var plugin = story.addPlugin("marketplace", {
					uploader: {
						settings: {
							url: "<?php echo JRoute::_('index.php') . '?option=com_easysocial&controller=marketplaces&task=uploadPhotos&format=json&tmpl=component&' . ES::token() . '=1'; ?>",
							max_file_size: "<?php echo $maxFileSize; ?>",
							camera: "image"
						}
					},
					"errors": {
						"-601": "<?php echo JText::_('COM_EASYSOCIAL_INVALID_FILE_UPLOADED', true);?>",
						"-600": "<?php echo JText::_('COM_EASYSOCIAL_FILE_SIZE_ERROR', true);?>",

						"messages": {
							"noEmptyAllowed": "<?php echo JText::_('COM_ES_MARKETPLACE_PHOTO_NOTE_MESSAGE', true);?>",
							"errorNumeric": "<?php echo JText::_('PLG_FIELDS_PRICE_VALIDATION_INPUT_NUMERIC', true);?>",
							"title": "<?php echo JText::_('COM_ES_MARKETPLACES_STORY_INSERT_TITLE', true);?>"
						}
					},
					"allowCondition": "<?php echo $allowCondition; ?>",
					"allowStock": "<?php echo $allowStock; ?>"
				});
});
