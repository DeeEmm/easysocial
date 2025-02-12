
EasySocial.require()
.script('site/activities/default')
.done(function($){
	$('[data-activities]').implement(EasySocial.Controller.Activities);
});
