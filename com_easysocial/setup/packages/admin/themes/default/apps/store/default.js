EasySocial.require()
.script('admin/vendors/raty', 'admin/apps/store')
.done(function($) {

	$('[data-apps-store]').implement(EasySocial.Controller.Apps.Store);

	$('[data-ratings]').raty({
		readOnly: true,
		score: function() {
			return $(this).data('score');
		}
	});

	<?php if (!$isSearch && !$apps) { ?>
		EasySocial.ajax('admin/controllers/store/refresh')
		.done(function() {
			window.location = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=apps&layout=store';

		}).fail(function(message) {
			$('[data-apps-wrapper]').addClass('has-error');
			$('[data-apps-error]').html(message);
		});
	<?php } ?>
});
