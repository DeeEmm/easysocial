EasySocial.ready(function($) {

	<?php if ($this->tmpl == 'component') { ?>
		
		$('[data-genre-insert]').on('click', function(event) {
			
			event.preventDefault();

			// Supply all the necessary info to the caller
			var element = $(this);
			var data = {
						"id": element.data('id'),
						"title" : element.data('title'),
						"alias" : element.data('alias')
					};

			window.parent["<?php echo $this->input->get('jscallback', '', 'cmd');?>" ](data);
		});
		
	<?php } else { ?>
		$.Joomla('submitbutton', function(task) {

			if (task == 'add') {
				window.location = '<?php echo JURI::root();?>administrator/index.php?option=com_easysocial&view=audiogenres&layout=form';
				return;
			}

			if (task == 'remove') {

				EasySocial.dialog({
					content: EasySocial.ajax("admin/views/audiogenres/confirmDelete"),
					bindings: {
						"{submit} click": function() {
							$.Joomla('submitform', ['delete']);
						}
					}
				});

				return false;
			}

			$.Joomla('submitform', [task]);
		});
	<?php } ?>

});