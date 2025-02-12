EasySocial.require()
.script('admin/workflows/form')
.done(function($){

	var controller = $('[data-workflows-form]').addController(EasySocial.Controller.Workflows, {
		"id" : '<?php echo $workflow->id; ?>',
		"tmpSteps": '<?php echo addslashes(json_encode($steps)); ?>'
	});

	$('[data-workflow-edit-heading]').click(function() {
		controller.openWorkflowConfig();
	});

	$.Joomla('submitbutton', function(task) {

		if (task == 'apply' || task == 'save' || task == 'savenew' || task == 'savecopy') {
			// Get the controller to save
			controller.save()
				.done(function() {
					$.Joomla('submitform', [task]);
				}).fail(function() {
					return false;
				});

			return false;
		}

		if (task == 'cancel') {
			window.location = '<?php echo $customRedirect;?>';
			return false;
		}

		$.Joomla('submitform', [task]);
	});

});
