EasySocial.ready(function($) {

	$(document)
		.on('click.facebook.revoke', '[data-facebook-revoke]', function() {
			var button = $(this);
			var callback = button.data('callback');

			EasySocial.dialog({
				"content": EasySocial.ajax('site/views/oauth/confirmRevoke', { "client" : 'facebook' , "callbackUrl" : callback})
			});
		});	
});