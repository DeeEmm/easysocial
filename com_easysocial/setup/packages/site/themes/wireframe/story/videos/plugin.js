EasySocial.require()
.script("site/story/videos")
.done(function($){
	var plugin =
		story.addPlugin("videos", {
			"uploader": {
				settings: {
					url: "<?php echo FRoute::raw('index.php?option=com_easysocial&controller=videos&task=uploadStory&uid=' . $uid. '&type=' . $type . '&format=json&tmpl=component&' . ES::token() . '=1' ); ?>",
					max_file_size: "<?php echo $uploadLimit; ?>",
					camera: "video",
					multi_selection: false,
					allowed: ['mp4', 'm4z', 'mov', 'wmv', 'avi', 'mpg', 'ogv', '3gpp', '3gp', '3g2', 'ogg', 'webm', 'x-flv', 'quicktime', 'x-msvideo', 'x-ms-wmv', 'x-m4v', 'x-matroska']
				}
			},
			"video": {
				"uid": "<?php echo $uid;?>",
				"type": "<?php echo $type;?>"<?php echo ($video->id) ? ',' : '';?>
				<?php if ($video->id) { ?>
				"id": "<?php echo $video->id;?>",
				"title": "<?php echo ES::string()->escape($video->title);?>",
				"link": "<?php echo ($video->source == 'link') ? ES::string()->escape($video->path) : '';?>",
				"source": "<?php echo $video->source; ?>",
				"isEncoding": false,
				<?php } ?>
			},
			"isEdit": <?php echo $isEdit ? 'true' : 'false'; ?>,
			"errors": {
				"-600": "<?php echo JText::sprintf('COM_EASYSOCIAL_VIDEOS_FILESIZE_ERROR', str_ireplace('M', 'MB', $uploadLimit));?>",

				"messages": {
					"insert": "<?php echo JText::_('COM_EASYSOCIAL_VIDEOS_STORY_CLICK_INSERT_VIDEO', true);?>",
					"empty": "<?php echo JText::_('COM_EASYSOCIAL_VIDEOS_STORY_NO_VIDEO_DETECTED', true);?>",
					"processing": "<?php echo JText::_('COM_EASYSOCIAL_VIDEOS_STORY_PROCESSING_VIDEO', true);?>",
					"category": "<?php echo JText::_('COM_EASYSOCIAL_VIDEOS_STORY_SELECT_CATEGORY', true);?>",
					"title": "<?php echo JText::_('COM_ES_VIDEOS_STORY_INSERT_TITLE', true);?>",
					"invalid": "<?php echo JText::_('COM_ES_VIDEOS_STORY_INVALID_FORMAT', true);?>"
				}
			}
		});
});
