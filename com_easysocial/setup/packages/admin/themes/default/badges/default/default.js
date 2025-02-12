
EasySocial.ready(function($){

	<?php if ($this->tmpl != 'component') { ?>
	$.Joomla('submitbutton', function(task) {

		if (task == 'add') {
			window.location = '<?php echo JURI::base();?>index.php?option=com_easysocial&view=badges&layout=form';
			return false;
		}

		if (task == 'remove') {
			EasySocial.dialog({
				content: EasySocial.ajax('admin/views/badges/confirmDelete'),
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

		$('[data-badge-insert]').on('click', function(event) {
			event.preventDefault();

			// Supply all the necessary info to the caller
			var id 		= $( this ).data( 'id' ),
				avatar 	= $( this ).data( 'avatar' ),
				title	= $( this ).data( 'title' ),
				alias	= $(this).data( 'alias' ),
				obj 	= {
							"id"	: id,
							"title"	: title,
							"avatar" : avatar,
							"alias"	: alias
						  },
				args 	= [ obj <?php echo $this->input->get('callbackParams', '', 'default') != '' ? ',' . ES::json()->encode( $this->input->get('callbackParams', '', 'default') ) : '';?>];

			window.parent["<?php echo $this->input->get('jscallback', '', 'cmd');?>" ].apply( obj , args );
		});

	<?php } ?>
});
