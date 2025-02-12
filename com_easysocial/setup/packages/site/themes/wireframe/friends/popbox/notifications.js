
EasySocial.require()
.script('site/toolbar/friends')
.done(function($) {

	$('[data-popbox-notifications-friends]').implement(EasySocial.Controller.Notifications.Friends.Popbox, {
		"messages": {
			"rejected": "<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_REQUEST_REJECTED', true);?>"
		}
	});
});