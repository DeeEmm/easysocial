EasySocial.require()
.script('site/mobile/filters')
.script('site/audios/filter')
.done(function($) {

	$('body').addController(EasySocial.Controller.Audios.Filter, {
		"uid": "<?php echo $uid;?>",
		"type": "<?php echo $type;?>",
		"active": "<?php echo $filter;?>"
	});

	$('[data-es-mobile-filters]').addController(EasySocial.Controller.Mobile.Filters);
});
