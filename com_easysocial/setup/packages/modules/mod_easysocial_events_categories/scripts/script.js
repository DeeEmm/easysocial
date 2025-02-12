EasySocial
.require()
.script('shared/sidebarmenu')
.done(function($){
	$('[data-sidebar-menu]').addController('EasySocial.Controller.Sidebarmenu', {
		animate: false
	});
});