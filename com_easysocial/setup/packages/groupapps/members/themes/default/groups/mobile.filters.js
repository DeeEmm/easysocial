EasySocial.require()
.script('site/mobile/filters')
.done(function($) {
	$('[data-es-group-members-filter]').addController(EasySocial.Controller.Mobile.Filters);
});
