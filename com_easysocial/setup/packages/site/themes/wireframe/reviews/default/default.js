
EasySocial.require()
.script('site/apps/reviews/reviews')
.done(function($) {
	$('[data-es-reviews]').implement(EasySocial.Controller.Apps.Review, {
		"id": "<?php echo $cluster->id; ?>"
	});
});