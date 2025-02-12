EasySocial.ready(function($) {

	$(document)
		.on('click.twitch.revoke', '[data-twitch-revoke]', function() {
			var button = $(this);
			var callback = button.data('callback');

			EasySocial.dialog({
				"content": EasySocial.ajax('admin/views/oauth/confirmRevoke', { "client" : 'twitch' , "callbackUrl" : callback})
			});
		});
});
