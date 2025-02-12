EasySocial.require()
.script('admin/users/form', 'shared/fields/validate')
.done(function($) {

	var form = $('[data-events-form]');

	form.implement('EasySocial.Controller.Users.Form', {
		mode: 'adminedit'
	});

	<?php if (!$isNew) { ?>

	form.find('[data-tabnav]').click(function(event) {
		var name = $(this).data('for');

		form.find('[data-active-tab]').val(name);
	});

	<?php } ?>

	$.Joomla('submitbutton', function(task) {
		if (task === 'cancel') {
			window.location = "<?php echo FRoute::url(array('view' => 'marketplaces')); ?>";

			return false;
		}

		var dfd = [];

		dfd.push(form.validate());

		$.when.apply(null, dfd)
			.done(function() {
				$.Joomla('submitform', [task]);
			})
			.fail(function() {
				EasySocial.dialog({
					content: EasySocial.ajax('admin/views/users/showFormError')
				});
			});
	});
});
