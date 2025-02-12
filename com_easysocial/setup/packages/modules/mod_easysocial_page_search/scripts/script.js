EasySocial.ready(function($) {

	EasySocial.require()
	.library('select2')
	.done(function($){

		$('[data-page-category]').select2();
		$('[data-page-author]').select2();
		$('[data-day]').select2();

		$('[data-day]').on('select2:close', function(i, e) {

			// get all the selected options
			var showAll = true;

			$('[data-day] option:selected').each(function (idx, ele) {
				var optionVal = $(ele).val();
				if (optionVal != 'all') {
					showAll = false;
				}
			});

			if (showAll) {
				$('[data-day] option:first').attr('selected', true);
			} else {
				$('[data-day] option:first').attr('selected', false);
			}

			$('[data-day]').trigger('change');
		});

		$('[data-pagesearch-button]').click(function(ev) {

			var start = $('[data-start]').val();
			var end = $('[data-end]').val();

			var invalidTime = $('[data-MOD_EASYSOCIAL_PAGE_SEARCH_NOTICE_INVALID_TIME]').text();

			if (start == end || start > end) {
				$('[data-pagesearch-notice]')
					.removeClass('t-hidden')
					.text(invalidTime);

				return false;
			}

			$('[data-pagesearch-notice]')
				.addClass('t-hidden')
				.text('');

			return true;
		});


		// $(document)
		// 	.on('change.hours.day', '[data-day]', function() {

		// 		var dayCheckbox = $(this);
		// 		var dayValue = dayCheckbox.val();

		// 		var container = $(this).closest('[data-days-container]');
		// 		var dayDiv = $(this).closest('[data-day-wrapper]');

		// 		if (dayValue == 'all') {
		// 			// hide start end inputs.
		// 			$(dayDiv).find('[hour-startend-wrapper]').addClass('t-hidden');

		// 		} else {
		// 			$(dayDiv).find('[hour-startend-wrapper]').removeClass('t-hidden');
		// 			$(dayDiv).find('[data-end] option:last').attr('selected', 'selected');
		// 		}

		// 	});


		// When any of the hours input is focused
		// $(document)
		// 	.on('click.hours.add', '[data-hours-add]', function() {
		// 		var container = $(this).closest('[data-days-container]');
		// 		var dayDiv = $(this).closest('[data-day-wrapper]');
		// 		var cloneDiv = $(dayDiv).clone();


		// 		$(cloneDiv).find('[data-day] option:first').attr('selected', 'selected');
		// 		$(cloneDiv).find('[data-start] option:first').attr('selected', 'selected');
		// 		$(cloneDiv).find('[data-end] option:last').attr('selected', 'selected');
		// 		$(cloneDiv).find('[hour-startend-wrapper]').addClass('t-hidden');

		// 		$(container).append(cloneDiv);

		// 	});

		// $(document)
		// 	.on('click.hours.remove', '[data-hours-remove]', function() {
		// 		// make sure we have atleast one day input.
		// 		var num = $(".mod-es-page-search-form [data-day-wrapper]").length;
		// 		if (num > 1) {
		// 			$(this).closest('[data-day-wrapper]').remove();
		// 		}

		// 	});

	});

});
