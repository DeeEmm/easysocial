<?php if ($loadScript) { ?>
EasySocial.ready(function($) {
	$('[data-colorpicker-revert]').on('click', function() {
		var button = $(this);
		var revert = button.data('color');
		var input = button.parent().find('input');

		input.val(revert);
		input.trigger('paste.minicolors');
	});
});
<?php } ?>
