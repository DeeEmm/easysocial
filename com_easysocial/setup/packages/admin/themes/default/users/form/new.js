EasySocial.require()
.script('admin/users/form', 'shared/fields/validate')
.done(function($)
{
	$('[data-user-form]').implement(EasySocial.Controller.Users.Form, {
		mode: 'register'
	});

	$.Joomla('submitbutton', function(task) {
		if (task == 'cancel') {
			window.location = 'index.php?option=com_easysocial&view=users';
			return false;
		}

		// Create an array of deferreds so that any one else can add in their saving process before joomla submits to the controller
		var dfd = [];

		// Validate the custom fields
		dfd.push($('[data-user-form]').validate());

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