EasySocial.require()
	.script('admin/grid/roworder')
	.done(function($) {
		$('[data-grid-column]').implement(EasySocial.Controller.Grid.Roworder);
	});