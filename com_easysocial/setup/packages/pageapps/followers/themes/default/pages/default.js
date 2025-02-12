
EasySocial.require()
.script('site/pages/followers')
.done(function($) {
	$('[data-es-page-followers]').implement(EasySocial.Controller.Pages.App.Followers);
})