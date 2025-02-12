EasySocial
.require()
.script('site/pages/browser')
.done(function($){
	$('[data-es-pages]').implement(EasySocial.Controller.Pages.Browser);
});
