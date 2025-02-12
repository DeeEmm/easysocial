EasySocial.require()
.script('site/audios/player.mini')
.done(function($) {
	$('[data-audio-player]').implement(EasySocial.Controller.Audios.PlayerMini);
});


