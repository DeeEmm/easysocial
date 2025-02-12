EasySocial
.require()
.script('site/discussions/filter')
.done(function($) {
	$('body').addController(EasySocial.Controller.Discussions.Filter);
});
