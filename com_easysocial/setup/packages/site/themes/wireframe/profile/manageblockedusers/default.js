EasySocial.require()
.script('site/profile/privacy')
.done(function($){
	$('[data-edit-privacy]').implement(EasySocial.Controller.Profile.Privacy);
});
