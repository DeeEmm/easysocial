EasySocial
	.require()
	.script('apps/fields/user/gender/content')
	.done(function($) {
		$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Gender', {
			required: <?php echo $field->required ? 1 : 0; ?>
		});
	});
