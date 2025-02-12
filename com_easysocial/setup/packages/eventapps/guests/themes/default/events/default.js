EasySocial.require()
.script('site/events/guests')
.done(function($) {
	$('[data-es-event-guests]').implement(EasySocial.Controller.Events.App.Guests);
})
