
EasySocial.require()
.script('site/groups/members')
.done(function($) {
	$('[data-es-group-members]').implement(EasySocial.Controller.Groups.App.Members);
})
