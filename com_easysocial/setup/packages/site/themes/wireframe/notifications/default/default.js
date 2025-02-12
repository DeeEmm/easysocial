
EasySocial.require()
.script('site/notifications/list')
.done(function($) {
	$('[data-notifications-list]').implement(EasySocial.Controller.NotificationsList);
});
