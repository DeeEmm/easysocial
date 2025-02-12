EasySocial.require()
.script('site/mobile/filters')
.done(function($) {

	// Mobile Filters
	$('[data-es-mobile-filters]').addController(EasySocial.Controller.Mobile.Filters);
});
