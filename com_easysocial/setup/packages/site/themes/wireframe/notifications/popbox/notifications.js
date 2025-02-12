EasySocial
.require()
.script('site/toolbar/system')
.done(function($) {
	$('[data-es-system-notifications]').addController(EasySocial.Controller.Notifications.System.Popbox);
});
