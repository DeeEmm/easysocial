EasySocial
.require()
.script('site/discussions/browser')
.done(function($) {
	$('[data-es-discussions][data-es-container]').addController(EasySocial.Controller.Discussions.Browser, {
		"clusterId": "<?php echo $cluster ? $cluster->id : '' ?>",
		"clusterType": "<?php echo $cluster ? $cluster->getType() : '' ?>"
	});
});
