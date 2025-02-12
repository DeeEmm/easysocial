
EasySocial.require()
.script('site/audios/process')
.done(function($){

	// Implement the processing controller here.
	$('[data-audio-process]').implement(EasySocial.Controller.Audios.Process);
});