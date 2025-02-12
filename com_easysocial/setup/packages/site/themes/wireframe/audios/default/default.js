EasySocial.require()
.script('site/audios/browser')
.done(function($){

	// Implement audios listing controller
	$('[data-audios-listing]').implement(EasySocial.Controller.Audios.Browser, {
		"uid": "<?php echo $uid;?>",
		"type": "<?php echo $type;?>",
		"active": "<?php echo !$filter ? 'all' : $filter;?>"
	});
});
