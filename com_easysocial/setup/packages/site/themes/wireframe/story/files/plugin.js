EasySocial.require()
.script("site/story/files")
.done(function($){

	var plugin =
		story.addPlugin("files", {
			"settings": {
				url: "<?php echo ESR::raw('index.php?option=com_easysocial&controller=explorer&task=hook&hook=addFile&uid=' . $uid . '&type=' . $type . '&format=json&tmpl=component&createStream=0&' . ES::token() . '=1' ); ?>",
				max_file_size: "<?php echo $maxFileSize; ?>",
				filters: [{extensions: "<?php echo $allowedExtensions;?>"}]
			},
			"errors": {
				"-601": "<?php echo JText::_('COM_EASYSOCIAL_INVALID_FILE_UPLOADED', true);?>",
				"-600": "<?php echo JText::_('COM_EASYSOCIAL_FILE_SIZE_ERROR', true);?>"
			},
			uid: "<?php echo $uid; ?>",
			utype: "<?php echo $type; ?>"
		});
});
