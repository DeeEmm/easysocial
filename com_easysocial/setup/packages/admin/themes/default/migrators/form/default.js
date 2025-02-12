EasySocial.require()
.script('admin/migrators/migrator')
.done(function($) {
	
	// Implement discover controller.
	$('[data-es-migrator]').implement(EasySocial.Controller.Migrators.Migrator, {
		component: "<?php echo $type;?>"
	});

	// Handle submit button.
	$.Joomla('submitbutton', function(action) {

		if (action == '<?php echo $type;?>') {
			EasySocial.dialog({
				"content": EasySocial.ajax('admin/views/migrators/confirmPurge', {"type": "<?php echo $type;?>"}),
				"bindings": {
					"{submitButton} click" : function() {
						Joomla.submitform([action]);
					}
				}
			});

			return false;
		}
		
		$.Joomla('submitform', [action]);
	});
});
