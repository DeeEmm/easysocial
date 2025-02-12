EasySocial.module('fields/user/separator/sample', function($) {
	var module = this;

	EasySocial.Controller( 'Field.Separator.Sample',
	{
		defaultOptions:
		{
			"{items}"	: "[data-separator-type]"
		}
	},
	function( self )
	{
		return {
			"{self} onConfigChange" : function( el , event , name , value )
			{
				if( name == 'type' )
				{
					var itemToShow 	= $( '[data-separator-' + value + ']' );

					// Hide all separators
					self.items().hide();

					// Only show the correct separator
					itemToShow.show();
				}

			}
		}
	});

	module.resolve();
});
