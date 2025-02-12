
EasySocial.require()
.script('site/audios/form')
.done(function($) {

	$('[data-audios-form]').implement(EasySocial.Controller.Audios.Form, {
		"type": "<?php echo $audio->type; ?>",
		"uid": "<?php echo $audio->uid; ?>",
		"isPrivateCluster": "<?php echo $isPrivateCluster; ?>",
		"defaultAlbumart": "<?php echo $defaultAlbumart; ?>",
		"importMetadata": "<?php echo $this->config->get('audio.autoimportdata'); ?>",
		"emptyUserTagsMessage": "<?php echo $this->config->get('friends.enabled') ? JText::_('COM_EASYSOCIAL_FRIENDS_SUGGEST_HINT_SEARCH') : JText::_('COM_EASYSOCIAL_FRIENDS_SUGGEST_HINT_SEARCH_NON_FRIEND'); ?>",
		<?php if ($userTagItemList) { ?>
		"tagsExclusion": <?php echo ES::json()->encode($userTagItemList); ?>
		<?php } ?>
	});

});
