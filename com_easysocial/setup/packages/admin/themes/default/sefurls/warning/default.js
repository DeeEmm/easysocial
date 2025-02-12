EasySocial.require()
.done(function($){

	$('[data-sef-warning-btn]').on('click.warning',function() {
		EasySocial.ajax('admin/views/sefurls/hideWarning')
		.done(function(state) {
			$('[data-warning-container]').closest('div.o-alert').hide();
		});
	});
});
