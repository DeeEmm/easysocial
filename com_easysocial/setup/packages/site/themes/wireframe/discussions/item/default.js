EasySocial.require()
.script('site/apps/discussions/discussions', 'site/vendors/prism')
.done(function($) {
	$('[data-discussion-item]').implement(EasySocial.Controller.Apps.Discussion.Item);
});