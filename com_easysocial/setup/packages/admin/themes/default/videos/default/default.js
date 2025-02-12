
EasySocial.ready(function($) {


	<?php if ($this->tmpl == 'component') { ?>

		$('[data-video-insert]').on('click', function(event) {

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

		$.Joomla('submitbutton', function(task){

			var ids = [];

			$('[data-table-grid]').find('input[name=cid\\[\\]]:checked').each(function(i, el) {
				var val = $(el).val();
				ids.push(val);
			});

			if (task == 'remove') {
				EasySocial.dialog({
					"content": EasySocial.ajax('admin/views/videos/confirmDelete', {"ids": ids})
				});

				return;
			}

			if (task == 'switchOwner') {
				EasySocial.dialog({
					content: EasySocial.ajax('admin/views/videos/switchOwner', { "ids" : ids })
				});
				return false;
			}

			$.Joomla('submitform', [task]);
		});

		window.switchOwner = function(user, ids)
		{
			EasySocial.dialog(
			{
				content : EasySocial.ajax('admin/views/videos/confirmSwitchOwner', { "id" : ids, "userId" : user.id}),
				bindings :
				{

				}
			});
		}
	<?php } ?>

});
