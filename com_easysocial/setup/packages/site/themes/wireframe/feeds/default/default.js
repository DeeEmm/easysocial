
EasySocial.require()
.script('site/apps/feeds/feeds')
.done(function($) {
	$('[data-feeds]').implement(EasySocial.Controller.Apps.Feeds);
});
