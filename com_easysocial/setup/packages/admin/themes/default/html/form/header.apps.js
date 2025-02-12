EasySocial.require()
.script('admin/header/apps')
.done(function($){

	var controller = $('[data-content-apps-header]').addController(EasySocial.Controller.Header.Apps);
});
