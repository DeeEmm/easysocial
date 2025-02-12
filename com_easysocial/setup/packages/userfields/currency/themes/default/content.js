EasySocial
.require()
.script('apps/fields/user/currency/content')
.done(function($) {
	$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Currency', {
		required: <?php echo $field->required ? 1 : 0; ?>
	});
});