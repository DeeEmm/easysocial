EasySocial.require()
.script('site/search/default')
.done(function($){
	$('[data-search-list]').implement(EasySocial.Controller.Search.List);
});
