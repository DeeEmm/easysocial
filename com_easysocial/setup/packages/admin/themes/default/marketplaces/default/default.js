EasySocial
.require()
.script('admin/api/toolbar')
.done(function($) {

	<?php if ($this->tmpl != 'component') { ?>
	$.Joomla('submitbutton', function(task) {
		var selected = [];

		$('[data-table-grid]').find('[data-table-grid-id]:checked').each(function(i, el) {
			selected.push($(el).val());
		});

		if (task === 'create') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/marketplaces/createDialog'),
				bindings: {
					'{continueButton} click': function() {
						var categoryId = this.category().val();

						window.location = 'index.php?option=com_easysocial&view=marketplaces&layout=form&category_id=' + categoryId;
					}
				}
			});

			return false;
		}

		if (task == 'setFeatured' || task == 'removeFeatured') {
			$('[data-table-grid-task]').val(task);

			$('[data-table-grid]').submit();

			return false;
		}

		if (task === 'delete') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/marketplaces/deleteDialog'),
				bindings: {
					'{deleteButton} click': function() {
						$.Joomla('submitform', [task]);
					}
				}
			});

			return false;
		}

		if (task === 'switchOwner') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/marketplaces/switchOwner', {
					ids: selected
				})
			});

			return false;
		}

		if (task === 'switchCategory') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/marketplaces/switchCategory', {
					ids: selected
				})
			});

			return false;
		}

		$.Joomla('submitform', [task]);
	});

	window.switchOwner = function(user, listingIds) {
		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/marketplaces/confirmSwitchOwner', {
				ids: listingIds,
				userId: user.id
			})
		});
	}
	<?php } else { ?>

		$('[data-item-insert]').on('click', function(event){
			event.preventDefault();

			// Supply all the necessary info to the caller
			var id = $(this).data('id'),
				title = $(this).data('title'),
				alias = $(this).data('alias');

				obj = {
						"id": id,
						"title": title,
						"alias": alias
					};

			window.parent["<?php echo $this->input->get('jscallback', '', 'cmd');?>" ]( obj );
		});

	<?php } ?>
});
