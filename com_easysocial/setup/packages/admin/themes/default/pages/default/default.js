EasySocial
.require()
.script('admin/api/toolbar')
.done(function($) {

	<?php if($this->tmpl != 'component'){ ?>
	$.Joomla('submitbutton', function(task) {

		var selected = new Array;

		$('[data-table-grid]').find('input[name=cid\\[\\]]:checked').each(function(i, el) {
			var val = $(el).val();
			selected.push(val);
		});

		if (task == 'makeFeatured' || task == 'removeFeatured') {

			$('[data-table-grid-task]').val(task);

			$('[data-table-grid]').submit();

			return false;
		}

		if (task == 'create') {
			
			EasySocial.dialog({
				content 	: EasySocial.ajax('admin/views/pages/createDialog' , {}),
				bindings	:
				{
					"{continueButton} click" : function()
					{
						var categoryId 	= this.category().val();

						window.location.href	= '<?php echo rtrim(JURI::root() , '/');?>/administrator/index.php?option=com_easysocial&view=pages&layout=form&category_id=' + categoryId;

						return false;
					}
				}
			});

			return false;
		}

		if (task == 'switchOwner') {
			EasySocial.dialog(
			{
				content		: EasySocial.ajax('admin/views/pages/switchOwner' , { "ids" : selected })
			});
			return false;
		}

		if (task == 'delete') {
			EasySocial.dialog(
			{
				content 	: EasySocial.ajax('admin/views/pages/deleteConfirmation' , {}),
				bindings	:
				{
					"{deleteButton} click" : function()
					{
						$.Joomla('submitform' , [task]);
					}
				}
			})
			return false;
		}

		if (task === 'switchCategory') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/pages/switchCategory', {
					ids: selected
				})
			});

			return false;
		}

		$.Joomla('submitform' , [task]);
	});

	window.switchOwner	= function(user , pageIds)
	{
		EasySocial.dialog(
		{
			content		: EasySocial.ajax('admin/views/pages/confirmSwitchOwner' , { "id" : pageIds , "userId" : user.id}),
			bindings 	:
			{

			}
		});
	}

	<?php } else { ?>
		$('[data-page-insert]').on('click', function(event)
		{
			event.preventDefault();

			// Supply all the necessary info to the caller
			var id 		= $(this).data('id'),
				avatar 	= $(this).data('avatar'),
				title	= $(this).data('title'),
				alias	= $(this).data('alias');

				obj 	= {
							"id"	: id,
							"title"	: title,
							"avatar" : avatar,
							"alias"	: alias
						  };

			window.parent["<?php echo $this->input->get('jscallback', '', 'cmd');?>" ](obj);
		});
	<?php } ?>
});
