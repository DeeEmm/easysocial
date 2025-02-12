EasySocial
.ready(function($) {

	var updatePaginationButton = function(button, counter) {
		var button = $(button);

		button.attr('data-birthday-paginate', counter);
		button.data('birthday-paginate', counter);

		if (counter == null) {
			button.attr('disabled', 'disabled');
			return;
		}

		button.removeAttr('disabled');
	};

	$(document).on('click.birthday.pagination', '[data-birthday-paginate]', function() {
		var limitstart = $(this).data('birthday-paginate');
		var module = $('[data-upcoming-birthday]');
		var moduleId = module.data('upcoming-birthday');

		EasySocial.ajax('site/views/users/getUpcomingBirthday', {
			'limitstart': limitstart,
			'moduleId': moduleId
		}).done(function(contents, currentPageCounter, previous, next) {

			console.log(previous.base);

			// Update contents
			module.find('[data-birthday-contents]').html(contents);

			// Update page counter
			module.find('[data-page-current]').html(currentPageCounter);

			// Update previous button
			updatePaginationButton(module.find('[data-previous]'), previous.base);

			// Update next button
			updatePaginationButton(module.find('[data-next]'), next.base);
		});
	});
});
