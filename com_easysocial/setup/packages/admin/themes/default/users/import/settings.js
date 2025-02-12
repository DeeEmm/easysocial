EasySocial.require()
.done(function($) {
	var fields = $('[data-field-select]');
	var passwordToggle = $('[data-password-settings-toggle]');
	var totalColumn = '<?php echo $totalColumn; ?>';
	var assigned = [];

	var passwordFieldId = '<?php echo $passwordFieldId; ?>';

	fields.on('change', function(ev) {
		var current = this;
		var id = $(current).data('id');
		var value = $(current).val();

		var previous = assigned[id];
		assigned[id] = value;

		$.each(fields, function(idx, el) {
			if (el == current) {
				return;
			}

			$(el).find('[data-id="' + value + '"]').attr('disabled', 'disabled');
			$(el).find('[data-id="' + previous + '"]').removeAttr('disabled');
		});
	});

	passwordToggle.on('change', function(ev) {
		var input = $(this);
		var checked = input.is(':checked');

		$('[data-password-settings]').toggleClass('t-hidden', checked);

		// Reset the password column
		var passwordSelection = $('[data-selection-field][data-id="' + passwordFieldId + '"]');
		if (checked) {
			$.each(fields, function(idx, el) {
				if ($(el).val() == passwordFieldId) {
					$(el).val('0');
				}
			});

			passwordSelection.attr('disabled', 'disabled');
		} else {
			passwordSelection.removeAttr('disabled');
		}
	});

	fields.trigger('change');
	passwordToggle.trigger('change');
});
