EasySocial.require()
.script('site/audios/player')
.done(function($) {
	$('[data-audio-player]').implement(EasySocial.Controller.Audios.Player);
});


