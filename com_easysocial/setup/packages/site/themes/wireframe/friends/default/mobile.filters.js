EasySocial.require()
.script('site/mobile/filters')
.script('site/friends/filter')
.done(function($) {

	$('body').addController(EasySocial.Controller.Friends.Filter);

	$('[data-es-mobile-filters]').addController(EasySocial.Controller.Mobile.Filters);
});
