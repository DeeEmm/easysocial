
EasySocial.require()
.script('site/polls/polls')
.done(function($){
	
	$('[data-polls-item]').implement("EasySocial.Controller.Polls.Vote", {
        "multiple": <?php echo ($poll->multiple) ? 'true' : 'false'; ?>,
        "guest": <?php echo $this->my->guest ? 'true' : 'false'; ?>
    });
});
