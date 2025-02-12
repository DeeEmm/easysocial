EasySocial.require()
.script('site/discussions/filter')
.script('site/mobile/filters')
.done(function($) {

	// Discussions Filters
	$('body').addController(EasySocial.Controller.Discussions.Filter);

	// Mobile Filters
	$('[data-es-mobile-filters]').addController(EasySocial.Controller.Mobile.Filters);
});

