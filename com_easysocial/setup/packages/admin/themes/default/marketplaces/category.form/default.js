EasySocial.ready(function($){

	$.Joomla('submitbutton', function(task) {
		if (task == 'cancel') {
			window.location	= 'index.php?option=com_easysocial&view=marketplaces&layout=categories';

			return false;
		}

		<?php if ($category->id) { ?>
		var performSave = function(id) {
			$.Joomla('submitform', [task]);
			return;
		}



		if (task == 'applyCategory' || task == 'saveCategory' || task == 'saveCategoryNew') {
			performSave(<?php echo $category->id; ?>);
			return false;
		}

		if (task == 'saveCategoryCopy') {
			// Make ajax call to create copy of category
			EasySocial.ajax('admin/controllers/marketplaces/createBlankCategory')
				.done(function(id) {

					// lets update the form element cid value.
					var input = $('input[name="cid"]');
					input.attr('value', id);
					performSave(id);
				});

			return false;
		}

		<?php } ?>

		$.Joomla('submitform', [task]);
	});

	$('[data-category-avatar-remove-button]').on('click', function() {
		var id = $(this).data('id'),
			button = $(this);

		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/marketplaces/confirmRemoveCategoryAvatar', { "id" : id }),
			bindings: {
				"{deleteButton} click" : function() {
					EasySocial.ajax('admin/controllers/marketplaces/removeCategoryAvatar', {
						"id" : id
					})
					.done(function() {
						$('[data-category-avatar-image]').remove();

						button.remove();

						EasySocial.dialog().close();
					});
				}
			}
		});
	});
});
