EasySocial.ready(function($){
	<?php if($this->tmpl != 'component'){ ?>
	$.Joomla('submitbutton', function(task) {

		if (task == 'add') {
			window.location = '<?php echo JURI::base();?>index.php?option=com_easysocial&view=ads&layout=advertiserForm';
			return false;
		}

		if (task == 'deleteAdvertiser') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/ads/confirmDeleteAdvertiser'),
				bindings: {
					"{deleteButton} click" : function() {
						$.Joomla('submitform', [task]);
					}
				}
			});

			return false;
		}

		$.Joomla('submitform', [task]);
	});

	<?php } else { ?>
		$('[data-advertiser-insert]').on('click', function(event) {
			event.preventDefault();

			// Supply all the necessary info to the caller
			var id = $(this).data('id'),
				title = $(this).data('title'),

				obj = {
					"id": id,
					"title": title,
				};

			window.parent["<?php echo $this->input->get('jscallback', '', 'cmd');?>"](obj);
		});
	<?php }?>
});
