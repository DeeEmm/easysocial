EasySocial
.require()
.script('site/followers/browser')
.done(function($) {

	$('[data-es-followers]').implement(EasySocial.Controller.Followers.Browser);

});
