
EasySocial.require()
.done(function($) {

	<?php if($this->tmpl != 'component'){ ?>
	$.Joomla('submitbutton', function(action) {
		if (action == 'remove') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/points/confirmDelete'),
				bindings:
				{
					"{deleteButton} click" : function() {
						$.Joomla('submitform', [action]);
					}
				}
			});

			return false;
		}

		$.Joomla('submitform', [action]);
	});

	<?php } else { ?>
		$('[data-points-insert]').on('click', function(event) {
			event.preventDefault();

			// Supply all the necessary info to the caller
			var id = $(this).data('id'),
				title = $(this).data('title'),
				alias = $(this).data('alias'),
				obj = {
							"id": id,
							"title": title,
							"alias": alias
						  },
				args = [obj <?php echo $this->input->get('callbackParams', '', 'default') != '' ? ',' . ES::json()->encode($this->input->get('callbackParams', '', 'default')) : '';?>];

			window.parent["<?php echo $this->input->get('jscallback', '', 'cmd');?>"].apply(obj, args);
		});
	<?php } ?>
});
