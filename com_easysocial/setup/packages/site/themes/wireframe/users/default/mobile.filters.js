EasySocial.require()
.script('site/mobile/filters')
.done(function($) {
	$('[data-es-mobile-filters]').addController(EasySocial.Controller.Mobile.Filters);
});
