EasySocial.ready(function($) {

	var wrapper = $('[data-login-image]');
	var removeButton = $('[data-login-image-remove-button]');
	var loginImage = $('[data-login-override-image]');
	var loginImageRemoveWrap = $('[data-login-image-remove-wrap]');

	$('[data-toggle-upload]').on('change', function() {
		var input = $(this);
		var checked = input.is(':checked');

		wrapper.toggleClass('t-hidden', !checked);
	});

	removeButton.on("click", function(el) {

		EasySocial.dialog({
			content: EasySocial.ajax('admin/views/settings/confirmRemoveImage'),
			bindings: {
				'{deleteButton} click': function() {

					EasySocial.ajax('admin/controllers/settings/deleteLoginImage').done(function() {

						loginImage.attr('src', wrapper.data('defaultLoginImage'));

						loginImageRemoveWrap.hide();

						EasySocial.dialog().close();
					});
				}
			}
		});
	})
});