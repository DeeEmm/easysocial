EasySocial.require()
.script('site/groups/filter')
.script('site/mobile/filters')
.done(function($) {

	$('body').addController(EasySocial.Controller.Groups.Filter);
	$('[data-es-mobile-filters]').addController(EasySocial.Controller.Mobile.Filters);
});
