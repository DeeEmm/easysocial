
EasySocial
.require()
.library( 'dialog' )
.done(function($){

	<?php if( $this->tmpl == 'component' ){ ?>		
		$( '[data-album-insert]' ).on('click', function( event )
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

			window.parent["<?php echo $this->input->get('jscallback', '', 'cmd');?>" ]( obj );
		});
		
	<?php } else { ?>
		
		$.Joomla( 'submitbutton' , function( task )
		{
			if( task == 'remove' )
			{
				EasySocial.dialog(
				{
					content 	: EasySocial.ajax( "admin/views/albums/confirmDelete" ),
					bindings	: 
					{
						"{deleteButton} click" : function()
						{
							$.Joomla( 'submitform' , [task] );

							return false;
						}
					}
				});
				return false;
			}

			$.Joomla( 'submitform' , [task] );
		});
	<?php } ?>

});