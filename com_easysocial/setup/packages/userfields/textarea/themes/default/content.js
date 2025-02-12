
EasySocial
	.require()
	.script('apps/fields/user/textarea/content')
	.done(function($) {
		$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Textarea', {
			required: <?php echo $field->required ? 1 : 0; ?>
		});
	});
