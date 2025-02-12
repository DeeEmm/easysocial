
EasySocial.ready(function($) {

	$.Joomla( 'submitbutton' , function( action ) 
	{
		if( action == 'remove' )
		{
			EasySocial.dialog(
			{
				content		: EasySocial.ajax( 'admin/views/points/confirmDelete' ),
				bindings 	:
				{
					"{deleteButton} click" : function()
					{
						$.Joomla( 'submitform' , [ action ] );
					}
				}
			});

			return false;
		}

		$.Joomla( 'submitform' , [ action ] );
	});
});