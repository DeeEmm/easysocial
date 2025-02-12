EasySocial.require()
.done(function($){

	$('[data-welcome-btn]').on('click.welcome',function() {
		
		EasySocial.ajax('site/views/dashboard/hideWelcome')
		.done(function(message) {
			$('[data-welcome-container]').html(message);
		});
	});
});
