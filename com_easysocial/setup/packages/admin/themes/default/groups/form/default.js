EasySocial.require()
.script('admin/users/form', 'shared/fields/validate', 'admin/clusters/users')
.done(function($) {

	var form = $('[data-groups-form]');

	form.implement(EasySocial.Controller.Users.Form, {
		mode: 'adminedit'
	});

	form.find('[data-tabnav]').click(function(event) {
		var name = $(this).data('for');

		form.find('[data-active-tab]').val(name);
	});

	$('[data-members-dropdown]').addController('EasySocial.Controller.Clusters.Users', {
		clusterid: <?php echo !$isNew ? $group->id : 0; ?>,
		clustertype: "groups",
		"error": {
			"empty": "<?php echo JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST', true);?>"
		}
	});

	$.Joomla('submitbutton', function(task) {
		if (task == 'cancel') {
			window.location	= 'index.php?option=com_easysocial&view=groups';

			return false;
		}

		// Create an array of deferreds so that any one else can add in their saving process before joomla submits to the controller
		var dfd = [];

		// Validate the custom fields
		dfd.push(form.validate());

		$.when.apply(null, dfd)
			.done(function() {
				$.Joomla('submitform', [task]);
			})
			.fail(function() {
				EasySocial.dialog(
				{
					content 	: EasySocial.ajax('admin/views/users/showFormError')
				});
			});
	});

	// Insert a new dropdown button on the toolbar
	$('[data-members-dropdown]')
		.removeClass('t-hidden')
		.appendTo('#toolbar');
});
