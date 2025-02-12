
EasySocial.require()
.script('site/videos/form')
.done(function($) {

	$('[data-videos-form]').implement(EasySocial.Controller.Videos.Form, {
		"type": "<?php echo $type; ?>",
		"uid": "<?php echo $video->uid; ?>",
		"isPrivateCluster": "<?php echo $isPrivateCluster; ?>",
		"uploadingText": "<?php echo JText::_('COM_ES_UPLOADING');?>",
		"emptyUserTagsMessage": "<?php echo $this->config->get('friends.enabled') ? JText::_('COM_EASYSOCIAL_FRIENDS_SUGGEST_HINT_SEARCH') : JText::_('COM_EASYSOCIAL_FRIENDS_SUGGEST_HINT_SEARCH_NON_FRIEND'); ?>",
		<?php if ($userTagItemList) { ?>
		"tagsExclusion": <?php echo ES::json()->encode($userTagItemList); ?>
		<?php } ?>
	});

});
