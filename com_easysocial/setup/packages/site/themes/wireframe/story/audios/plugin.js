EasySocial.require()
.script("site/story/audios")
.done(function($){
	var plugin =
		story.addPlugin("audios", {
			"uploader": {
				settings: {
					url: "<?php echo ESR::raw('index.php?option=com_easysocial&controller=audios&task=uploadStory&uid=' . $uid. '&type=' . $type . '&format=json&tmpl=component&' . ES::token() . '=1' ); ?>",
					max_file_size: "<?php echo $uploadLimit; ?>",
					multi_selection: false
				}
			},
			"audio": {
				"uid": "<?php echo $uid;?>",
				"type": "<?php echo $type;?>"<?php echo ($audio->id) ? ',' : '';?>
				<?php if ($audio->id) { ?>
				"id": "<?php echo $audio->id;?>",
				"title": "<?php echo ES::string()->escape($audio->title);?>",
				"album": "<?php echo ES::string()->escape($audio->album);?>",
				"artist": "<?php echo ES::string()->escape($audio->artist);?>",
				"link": "<?php echo ($audio->source == 'link') ? ES::string()->escape($audio->path) : '';?>",
				"source": "<?php echo $audio->source; ?>",
				"isEncoding": false,

				<?php } ?>
			},
			"isEdit": <?php echo $isEdit ? 'true' : 'false'; ?>,
			"errors": {
				"-600": "<?php echo JText::sprintf('COM_ES_AUDIO_FILESIZE_ERROR', str_ireplace('M', 'MB', $uploadLimit));?>",

				"messages": {
					"insert": "<?php echo JText::_('COM_ES_AUDIO_STORY_CLICK_INSERT_AUDIO', true);?>",
					"empty": "<?php echo JText::_('COM_ES_AUDIO_STORY_NO_AUDIO_DETECTED', true);?>",
					"processing": "<?php echo JText::_('COM_ES_AUDIO_STORY_PROCESSING_AUDIO', true);?>",
					"genre": "<?php echo JText::_('COM_ES_AUDIO_STORY_SELECT_GENRE', true);?>",
					"title": "<?php echo JText::_('COM_ES_AUDIO_STORY_INSERT_TITLE', true);?>"
				}
			}
		});
});
