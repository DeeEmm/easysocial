EasySocial.require()
.script('site/audios/playlist')
.done(function($) {
    $('[data-audio-playlist]').implement(EasySocial.Controller.Audios.Playlist, {
        playlistId: "<?php echo $activeList->id ?>",
        nowPlaying: "<?php echo JText::_('COM_ES_AUDIO_NOW_PLAYING'); ?>"
    });
});




