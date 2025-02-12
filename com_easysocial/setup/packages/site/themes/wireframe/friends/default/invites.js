EasySocial
.require()
.done(function($){

	$('[data-es-invitation-delete]').on('click', function() {
			
		var parent = $(this).closest('[data-item]');
		var id = parent.data('id');
	
		EasySocial.ajax( 'site/controllers/friends/deleteInvites', {
			"id": id
		}).done(function() {
			parent.remove();
		});
	});

	$('[data-es-invitation-resend]').on('click', function() {
			
		var parent = $(this).closest('[data-item]');
		var id = parent.data('id');
		var info = parent.find('[data-invitation-info]');

		EasySocial.ajax('site/controllers/friends/resendInvites', {
			"id": id
		}).done(function(newInfo) {
			info.html(newInfo);
		});
	});
});
