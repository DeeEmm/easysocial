EasySocial
.require()
.script('apps/fields/user/numeric/content')
.done(function($) {
	$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Numeric', {
		required: <?php echo $field->required ? 1 : 0; ?>
	});
});