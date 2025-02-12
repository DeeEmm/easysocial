
EasySocial.ready(function($) {

	$('[data-mailer-list]').implement(EasySocial.Controller.Mailer)

	// Handle submit button.
	$.Joomla('submitbutton' , function(action) {
		
		if (action == 'purgeAll') {
			EasySocial.dialog({
				"content": EasySocial.ajax('admin/views/mailer/confirmPurgeAll'),
				"bindings": {
					"{purgeButton} click" : function() {
						Joomla.submitform([action]);
					}
				}
			});

			return false;
		}

		if( action == 'purgeSent' )
		{
			EasySocial.dialog(
			{
				content 	: EasySocial.ajax( 'admin/views/mailer/confirmPurgeSent' ),
				bindings 	:
				{
					"{purgeButton} click" : function()
					{
						Joomla.submitform( [action ] );
					}
				}
			});
			return false;
		}

		if( action == 'purgePending' )
		{
			EasySocial.dialog(
			{
				content 	: EasySocial.ajax( 'admin/views/mailer/confirmPurgePending' ),
				bindings 	:
				{
					"{purgeButton} click" : function()
					{
						Joomla.submitform( [action ] );
					}
				}
			});
			return false;
		}

		$.Joomla( 'submitform' , [ action ] );
	});


	$(document)
		.on('click.preview.mail', '[data-mailer-item-preview]', function() {
			var link = $(this);
			var id = link.data('id');

			EasySocial.dialog({
				"content": EasySocial.ajax('admin/views/mailer/preview', {'id': id})
			});
		});

});
