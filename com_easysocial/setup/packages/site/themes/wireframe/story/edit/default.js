
EasySocial.require()
.script('site/story/story')
.done(function($) {

	// Story controller
	var story = $("[data-story=<?php echo $story->id; ?>]");
	story
		.addController("EasySocial.Controller.Story", {

			"errors": {
				"empty": "<?php echo JText::_('COM_EASYSOCIAL_STORY_CONTENT_EMPTY', true);?>",
				"filter": "<?php echo JText::_('COM_EASYSOCIAL_STORY_NOT_ON_STREAM_FILTER', true);?>",
				"standard": "<?php echo JText::_('COM_EASYSOCIAL_STORY_SUBMIT_ERROR', true);?>",
				"scheduled": "<?php echo JText::_('COM_ES_STORY_SCHEDULED_DATE_EMPTY', true);?>",
				"invalidScheduled": "<?php echo JText::_('COM_ES_STORY_SCHEDULED_DATE_INVALID', true);?>"
			},

			"moodText": "<?php echo JText::_('COM_EASYSOCIAL_MOOD_VERB_FEELING', true);?>",
			"scheduledPost": "<?php echo JText::_('COM_ES_STORY_SCHEDULED_A_POST', true);?>",

			<?php
			if ($story->plugins) {
				$length = count($story->plugins);
				$i = 0;
			?>
				plugin: {
					<?php foreach($story->plugins as $plugin) { ?>
					<?php echo $plugin->name; ?>: {
						id: '<?php echo $plugin->id; ?>',
						type: '<?php echo $plugin->type; ?>',
						name: '<?php echo $plugin->name; ?>'
					}<?php if (++$i < $length) { echo ','; }; ?>
					<?php } ?>
				},
			<?php } ?>

			<?php
			if ($mentions) {
				$length = count($mentions);
				$i = 0;
			?>
				mentionedItems: {
					<?php foreach($mentions as $mention) { ?>
					<?php echo $mention->id; ?>: {
						userId: '<?php echo $mention->uid; ?>',
						start: '<?php echo $mention->offset; ?>',
						length: '<?php echo $mention->length; ?>'
					}<?php if (++$i < $length) { echo ','; }; ?>
					<?php } ?>
				},
			<?php } ?>

			<?php
			if ($currentMood) {
			?>
				currentMood: {
						icon : "<?php echo $currentMood->icon; ?>",
						verb : "<?php echo $currentMood->verb; ?>",
						subject : "<?php echo $currentMood->subject; ?>",
						text : "<?php echo $currentMood->text; ?>",
						subjectText : "<?php echo $currentMood->subject; ?>",
						custom : "<?php echo $currentMood->custom; ?>"
				},
			<?php } ?>

			<?php if ($streamItemTable->hasGiphy()) { ?>
				currentGiphy: "<?php echo $streamItemTable->getGiphyUrl(); ?>",
			<?php } ?>

			<?php
			if ($currentLocation) {
			?>
				currentLocation: {
						address : "<?php echo $currentLocation->address; ?>",
						fulladdress : "<?php echo $currentLocation->address; ?>",
						latitude : "<?php echo $currentLocation->latitude; ?>",
						longitude : "<?php echo $currentLocation->longitude; ?>",
						name : "<?php echo $currentLocation->short_address; ?>"
				},
			<?php } ?>
				enterToSubmit: false,
				sourceView: "<?php echo $this->input->get('view', '', 'cmd'); ?>",
				hashtagEditable: "<?php echo $story->hashtagEditable; ?>",
				emoticons: '<?php echo $emoticons; ?>',
				mapIntegration: "<?php echo $this->config->get('location.provider'); ?>",
				mapElementId: "map-<?php echo $story->id; ?>"
			}
		);


	// Story plugins
	$.module("<?php echo $story->moduleId; ?>")
		.done(function(story) {
			<?php foreach($story->plugins as $plugin) { ?>
				<?php echo $plugin->script; ?>
			<?php } ?>
		});
});
