EasySocial.require()
.done(function($) {
	var appleSecretinput = $('[data-applesecret-input]');
	var appleSecretbutton = $('[data-applesecret-button]');

	appleSecretbutton.on('click', function() {
		var aTag = $(this).find( "a" );
		aTag.addClass('is-loading');

		EasySocial.ajax('admin/controllers/settings/generateJWTToken').done(function(result) {
			appleSecretinput.val(result);
		}).always(function() {
			aTag.removeClass('is-loading');
		})
	});
});
