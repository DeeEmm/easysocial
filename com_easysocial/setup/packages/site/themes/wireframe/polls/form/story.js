
EasySocial.require()
.script('site/polls/polls')
.done(function($){
	$('[data-polls-form]').implement(EasySocial.Controller.Polls.Form);
});
