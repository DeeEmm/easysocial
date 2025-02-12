EasySocial.require()
.script("site/story/broadcast")
.done(function($) {
	var plugin = story.addPlugin("broadcast", {
		"error": "<?php echo JText::_('APP_BROADCAST_STORY_FORM_EMPTY_MESSAGE', true);?>"
	});
});
