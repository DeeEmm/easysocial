EasySocial.require()
.script('site/users/default')
.done(function($) {
	$('[data-es-users]').implement(EasySocial.Controller.Users);
});
