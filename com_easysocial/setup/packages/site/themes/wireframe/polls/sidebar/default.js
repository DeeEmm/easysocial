EasySocial
.require()
.script('site/polls/filter')
.done(function($) {
	$('body').addController(EasySocial.Controller.Polls.Filter);
});
