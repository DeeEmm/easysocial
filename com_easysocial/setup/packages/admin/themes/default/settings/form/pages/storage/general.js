EasySocial
.require()
.done(function($){

	$('select[name="storage.photos"]').change(function() {

		if ($(this).val() == 'amazon') {
			$('[data-amazon-photos]').toggleClass('t-hidden', false);
		} else {
			$('[data-amazon-photos]').toggleClass('t-hidden', true);

			// reset the the checkbox
			$('input[name="storage.amazon.upload.photo"]').attr('checked', false);
			$('input[name="storage.amazon.upload.photo"]').prop('checked', false);
		}

	});

});
