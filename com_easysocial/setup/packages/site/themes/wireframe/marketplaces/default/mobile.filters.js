EasySocial
.require()
.script('site/mobile/filters', 'site/marketplaces/filter')
.done(function($) {

	$('body').addController(EasySocial.Controller.Marketplaces.Filters, {
		"userId": "<?php echo $activeUser ? $activeUser->id : '';?>"
	});

	$('[data-es-mobile-filters]').addController(EasySocial.Controller.Mobile.Filters);
});
