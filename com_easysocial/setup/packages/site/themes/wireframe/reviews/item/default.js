
EasySocial.require()
.script('site/apps/reviews/reviews')
.done(function($) {
	$('[data-review-item]').implement(EasySocial.Controller.Apps.Review);
});