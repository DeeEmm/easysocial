
EasySocial.require()
.script('site/apps/news/news')
.done(function($) {
	$('[data-news-item]').implement(EasySocial.Controller.Apps.News.Item);
})