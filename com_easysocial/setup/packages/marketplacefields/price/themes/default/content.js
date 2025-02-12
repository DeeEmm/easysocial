EasySocial
.require()
.script('apps/fields/marketplace/price/content')
.done(function($) {
	$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Price', {
		required: <?php echo $field->required ? 1 : 0; ?>
	});
});
