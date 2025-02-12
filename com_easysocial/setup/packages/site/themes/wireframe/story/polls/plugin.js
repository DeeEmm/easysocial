EasySocial.require()
.script("site/story/polls")
.done(function($) {
	var plugin = story.addPlugin("polls", {
		"error": "<?php echo JText::_('COM_EASYSOCIAL_POLLS_EMPTY_MESSAGE', true);?>"
	});
});
