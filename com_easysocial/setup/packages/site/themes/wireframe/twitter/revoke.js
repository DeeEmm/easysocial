EasySocial.ready(function($) {

	$(document)
		.on('click.twitter.revoke', '[data-twitter-revoke]', function() {
			var button = $(this);
			var callback = button.data('callback');

			EasySocial.dialog({
				"content": EasySocial.ajax('site/views/oauth/confirmRevoke', { "client" : 'twitter' , "callbackUrl" : callback})
			});
		});	
});