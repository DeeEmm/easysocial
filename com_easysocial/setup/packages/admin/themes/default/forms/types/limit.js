
EasySocial.require()
.done(function($)
{
	$( '[data-<?php echo str_ireplace( array( '[' , '.' , ']' ) , '' , $field->inputName );?>]' ).on( 'change' , function()
	{
		var value 	= $( this ).val(),
			element	= $( this ).parents( '[data-limit-form]' ).find( '[data-limit-limited]' );

		// If the state is "Limited" , we want to display the input
		if( value == 0 )
		{
			element.removeClass( 't-hidden' );
		}
		else
		{
			element.addClass( 't-hidden' );
			element.find( '[data-limit-input]' ).val( '0' );
		}
	});
});
