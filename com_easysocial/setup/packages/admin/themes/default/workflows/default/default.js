EasySocial
.require()
.script('admin/api/toolbar')
.done(function($){
	$.Joomla('submitbutton', function(task) {

		// Route to the appropriate controller
		if (task == 'remove' || task == 'duplicate') {
			$('input[name=controller]').val('workflows');

			$.Joomla('submitform', [task]);
			return;
		}

		if (task == 'add') {
			window.location = '<?php echo ESR::_('index.php?option=com_easysocial&view=workflows&layout=form&type=' . $type); ?>';
			return;
		}

		$.Joomla('submitform' , [task]);
	})
})
