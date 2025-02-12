EasySocial.require()
.script("site/explorer/explorer")
.done(function($){
	$("[data-fd-explorer=<?php echo $uuid;?>]")
	.explorer({
		"languages": {
			"create": "<?php echo JText::_('COM_EASYSOCIAL_EXPLORER_ENTER_FOLDER_NAME', true);?>",
			"invalid": "<?php echo JText::_('COM_EASYSOCIAL_EXPLORER_INVALID_FOLDER_NAME', true);?>"
		},
		"isMobile": <?php echo $this->isMobile() ? 'true' : 'false'; ?>,
		"allowedUpload": <?php echo $showUpload ? 'true' : 'false'; ?>,
	})
	.on("fileUse", function(event , id , file , data) {

	});
});