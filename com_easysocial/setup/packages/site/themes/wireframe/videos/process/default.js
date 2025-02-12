
EasySocial.require()
.script('site/videos/process')
.done(function($){

    $('[data-video-process]').implement(EasySocial.Controller.Videos.Process);
});
