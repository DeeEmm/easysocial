EasySocial.require()
.script('site/polls/filter')
.script('site/mobile/filters')
.done(function($) {

	$('body').addController(EasySocial.Controller.Polls.Filter);
	$('[data-es-slider]').addController(EasySocial.Controller.Mobile.Filters);
});
