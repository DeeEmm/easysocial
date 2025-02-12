EasySocial.require().script('site/search/dating').done(function($) {
	$('[data-mod-dating-search-item]').addController('EasySocial.Controller.Search.Dating');
});
