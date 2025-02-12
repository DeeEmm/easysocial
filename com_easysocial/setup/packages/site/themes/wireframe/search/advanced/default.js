EasySocial.require()
.script('site/search/advanced')
.done(function($) {
	$('[data-es-advanced]').implement(EasySocial.Controller.Search.Advanced, {
		"type": "<?php echo $type;?>"
	});
});
