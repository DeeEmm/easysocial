EasySocial.require()
.done(function($) {

	wrapper = $('[data-posttype-wrapper]');
	context = wrapper.data('context');

	if (context == 'user') {
		context = 'dashboard';
	}

	$(document).on('onAfterSelectPostType', 'body', function(event, val) {
		event.stopPropagation();

		var element = $('#mod-post-type-' + val);
		var checked = element.is(':checked');

		element.attr('checked', checked);
	});

	// Simulate the post types filter on the dashboard
	$('[data-es-mod-types]').on('change', function() {

		var controller = $('[data-es-' + context + ']').controller();

		// Set the flag that this action is from module
		controller.fromModule = true;

		var checked = $(this).is(':checked');
		var value = $(this).val();

		var postType = controller.postTypeFilter().filter(function() {
			if ($(this).val() == value) {
				return true;
			}
		});

		if (checked) {
			postType.click();
			return;
		}

		postType.click();
	});

});
