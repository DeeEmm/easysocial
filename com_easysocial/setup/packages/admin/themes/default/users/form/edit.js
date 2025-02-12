EasySocial.require()
.script('shared/fields/validate', 'admin/users/form')
.done(function($) {

	$('[data-user-form]').implement(EasySocial.Controller.Users.Form, {
		userid: <?php echo $user->id; ?>
	});

	$.Joomla('submitbutton', function(task){

		if (task == 'cancel') {
			window.location = 'index.php?option=com_easysocial&view=users';
			return false;
		}

		// Create an array of deferreds so that any one else can add in their saving process before joomla submits to the controller
		var dfd = [];

		var validation = $('[data-user-form]').validate();

		// Validate the custom fields
		dfd.push(validation);

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

	// Deletes a user's badge
	$('[data-delete-badge]').on('click', function() {

		var id = $(this).data('id');
		var userId = $(this).data('userid');

		EasySocial.dialog( {
			content: EasySocial.ajax( 'admin/views/users/confirmRemoveBadge' , { "id" : id , "userid" : userId } ),
			bindings: {
				"{deleteButton} click" : function() {
					this.deleteForm().submit();
				}
			}
		});
	});
});