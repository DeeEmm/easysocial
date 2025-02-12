EasySocial
.require()
.script('site/mobile/filters', 'site/pages/filter')
.done(function($) {

	$('body').addController(EasySocial.Controller.Pages.Filter, {
		"userId": "<?php echo $user ? $user->id : '';?>"
	});

	$('[data-es-mobile-filters]').addController(EasySocial.Controller.Mobile.Filters);
});
