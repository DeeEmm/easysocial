EasySocial.ready(function($){

	<?php if( !$callback ){ ?>
	$.Joomla("submitbutton", function(task) {
		if( task == 'form' )
		{
			window.location 	= 'index.php?option=com_easysocial&view=profiles&layout=form';

			return;
		}

		if( task == 'delete' )
		{

			EasySocial.dialog(
			{
				content 	: EasySocial.ajax( 'admin/views/profiles/confirmDelete' , {} ),
				bindings 	:
				{
					"{deleteButton} click" : function()
					{
						$.Joomla( 'submitform' , ['delete' ] );
					}
				}
			});

			return false;
		}

		$.Joomla("submitform", [task]);

	});

	<?php } else { ?>

		<?php if( $this->input->get('jscallback', '', 'cmd') ){ ?>
			$( '[data-profile-insert]' ).on('click', function( event )
			{
				event.preventDefault();

				// Supply all the necessary info to the caller
				var id 		= $( this ).data( 'id' ),
					avatar 	= $( this ).data( 'avatar' ),
					title	= $( this ).data( 'title' ),
					alias	= $(this).data( 'alias' );

					obj 	= {
								"id"	: id,
								"title"	: title,
								"avatar" : avatar,
								"alias"	: alias
							  };
					args 	= [ obj <?php echo $this->input->get( 'callbackParams', '', 'default' ) != '' ? ',' . ES::json()->encode( $this->input->get( 'callbackParams', '', 'default' ) ) : '';?>];

				window.parent["<?php echo $this->input->get('jscallback', '', 'cmd');?>" ].apply( obj , args );
			});
		<?php } else { ?>
			$( '[data-profile-insert]' ).on('click', function( event )
			{
				event.preventDefault();

				var id 	= $( this ).data( 'id' );

				window.parent["<?php echo $this->input->get('callback', '', 'cmd');?>" ]( id );
			});
		<?php } ?>

	<?php } ?>

});
