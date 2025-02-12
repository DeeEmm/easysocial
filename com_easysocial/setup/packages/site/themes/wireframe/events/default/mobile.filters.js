EasySocial.require()
.script('site/mobile/filters', 'site/events/filter')
.done(function($) {

	// Events Filters
	$('body').addController(EasySocial.Controller.Events.Filter);

	// Mobile Filters
	$('[data-es-mobile-filters]').addController(EasySocial.Controller.Mobile.Filters);
});
