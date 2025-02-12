EasySocial
.require()
.script('site/manage/clusters')
.done(function($){
	$('[data-es-cluster-wrapper]').addController(EasySocial.Controller.Clusters);
});
