EasySocial.require()
.script('site/mobile/filters')
.done(function($) {
	$('[data-es-slider]').addController(EasySocial.Controller.Mobile.Filters);
});
