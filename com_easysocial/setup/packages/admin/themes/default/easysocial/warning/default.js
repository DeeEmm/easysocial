EasySocial.require()
.done(function($){

	$('[data-langcheck-warning-btn]').on('click.langcheck.warning',function() {
		EasySocial.ajax('admin/views/easysocial/hideLangCheckWarning')
		.done(function(state) {
			$('[data-warning-container]').closest('div.o-alert').hide();
		});
	});
});
