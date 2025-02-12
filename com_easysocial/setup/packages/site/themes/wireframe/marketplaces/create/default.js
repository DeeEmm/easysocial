
EasySocial
.require()
.script('site/marketplaces/create')
.done(function($){
	$('[data-es-select-category]').implement(EasySocial.Controller.Marketplaces.Create);
});
