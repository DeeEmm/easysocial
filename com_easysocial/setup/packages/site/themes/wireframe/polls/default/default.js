EasySocial
.require()
.script('site/polls/browser')
.done(function($) {

	$('[data-es-polls][data-es-container]').addController(EasySocial.Controller.Polls.Browser, {
		"clusterId": "<?php echo $cluster ? $cluster->id : '' ?>",
		"clusterType": "<?php echo $cluster ? $cluster->getType() : '' ?>",
		"userId": "<?php echo $user ? $user->id : '' ?> "
	});
});
