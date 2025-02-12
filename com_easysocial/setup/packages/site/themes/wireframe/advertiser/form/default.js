EasySocial.ready(function($) {

$('[data-logo]').on('change', function() {

var el = $(this);
var label = el.val().replace(/\\/g, '/').replace(/.*\//, '');


$('[data-logo-title]').val(label);

});


<?php if ($advertiser && $advertiser->isPublished()) { ?>
$('[data-save-button]').on('click', function(event) {
event.preventDefault();
event.stopPropagation();

var form = $('[data-advertiser-form]');

EasySocial.dialog({
	'content': EasySocial.ajax('site/views/dialogs/render', {
		'file': 'site/advertiser/dialogs/edit.confirmation'
	}),
	'bindings': {
		"{submitButton} click": function() {
			form.submit();
		}
	}
})

});
<?php } ?>

});
