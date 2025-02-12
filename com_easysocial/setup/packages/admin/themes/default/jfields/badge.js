EasySocial.require()
.library( 'dialog' )
.done(function($) {
	window.selectBadge 	= function( obj ) {
		$( '[data-jfield-badge-title]' ).val( obj.title );

		$( '[data-jfield-badge-value]' ).val( obj.alias );

		// Close the dialog when done
		EasySocial.dialog().close();
	}

	$( '[data-jfield-badge]' ).on( 'click', function()
	{
		EasySocial.dialog(
		{
			content 	: EasySocial.ajax( 'admin/views/badges/browse' , { 'jscallback' : 'selectBadge' })
		});
	});

});
