EasySocial.require()
.script('site/mobile/filters')
.script('site/search/advanced')
.done(function($) {

	$('[data-es-advanced]').implement(EasySocial.Controller.Search.Advanced, {
		"type": "<?php echo $type;?>"
	});

	$('[data-es-mobile-filters]').addController(EasySocial.Controller.Mobile.Filters);
});
