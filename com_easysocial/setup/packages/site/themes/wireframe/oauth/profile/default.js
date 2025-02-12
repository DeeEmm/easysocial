
EasySocial.ready(function($) {
	
	$('[data-oauth-profile-submit]').on('click', function(event) {
		// Prevent the link from being accessed
		event.preventDefault();

		var id = $(this).data('id');

		$('[data-oauth-profile-id]' ).val(id);
		$('[data-oauth-profile]').submit();
	});
});
