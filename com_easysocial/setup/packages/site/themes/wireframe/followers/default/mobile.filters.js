EasySocial.require()
.script('site/mobile/filters')
.script('site/followers/filter')
.done(function($) {

	$('body').addController(EasySocial.Controller.Followers.Filter);

	$('[data-es-mobile-filters]').addController(EasySocial.Controller.Mobile.Filters);
});
