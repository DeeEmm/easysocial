EasySocial.require()
.script('site/badges/badge')
.done(function($) {
	$('[data-badge]').addController('EasySocial.Controller.Badges.Badge');
});
