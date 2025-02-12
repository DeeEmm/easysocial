EasySocial.require()
.script('site/videos/browser')
.done(function($){

	// Implement videos listing controller
	$('[data-videos-listing]').implement(EasySocial.Controller.Videos.Browser, {
		"uid": "<?php echo $uid;?>",
		"type": "<?php echo $type;?>",
		"active": "<?php echo !$filter ? 'all' : $filter;?>"
	});
});
