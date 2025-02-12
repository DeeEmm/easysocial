EasySocial.ready(function($) {

	$(document)
		.on('click.google.revoke', '[data-google-revoke]', function() {
			var button = $(this);
			var callback = button.data('callback');

			EasySocial.dialog({
				"content": EasySocial.ajax('site/views/oauth/confirmRevoke', { "client" : 'google' , "callbackUrl" : callback})
			});
		});
});
