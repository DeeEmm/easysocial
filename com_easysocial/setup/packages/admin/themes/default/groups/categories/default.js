EasySocial.ready(function($) {

	<?php if ($this->tmpl == 'component') { ?>
		$('[data-category-insert]').on('click', function(event) {
			event.preventDefault();

			// Supply all the necessary info to the caller
			var id = $(this).data('id'),
				avatar = $(this).data('avatar'),
				title = $(this).data('title'),
				alias = $(this).data('alias');

				obj = {
					"id" : id,
					"title" : title,
					"avatar" : avatar,
					"alias" : alias
				};

			window.parent["<?php echo $this->input->get('jscallback', '', 'cmd');?>" ](obj);
		});
		
	<?php } else { ?>
		$.Joomla('submitbutton', function(task) {
			if (task == 'deleteCategory') {
				EasySocial.dialog({
					content : EasySocial.ajax('admin/views/groups/confirmDeleteCategory', {}),
					bindings : {
						"{deleteButton} click" : function() {
							$.Joomla('submitform', [task]);
							return false;
						}
					}
				});

				return false;
			}

			if (task == 'categoryForm') {
				window.location.href = 'index.php?option=com_easysocial&view=groups&layout=categoryForm';
				return false;
			}

			$.Joomla('submitform', [task]);
		});
	<?php } ?>
});
