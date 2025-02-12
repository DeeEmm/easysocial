
EasySocial.require()
.script('site/pages/edit')
.done(function($) {
	$('[data-pages-edit]').implement(EasySocial.Controller.Pages.Edit, {
		"id" : "<?php echo $page->id;?>"
	});
});
