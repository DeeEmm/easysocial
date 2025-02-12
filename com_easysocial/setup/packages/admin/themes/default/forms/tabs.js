
EasySocial.ready(function($) {
	
	<?php if ($processActiveTab) { ?>
	$('[data-form-tabs-<?php echo $uid;?>]').on('click', function() {

		// Check to see if there's any data-tab-active input
		var currentInput = $('[data-tab-active]');

		if (currentInput) {
			var selected = $(this).data('item');
			currentInput.val(selected);
		}

	});
	<?php } ?>

});
