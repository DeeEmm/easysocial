EasySocial
	.require()
	.script('apps/fields/user/joomla_username/registermini_content')
	.done(function($) {
		$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Joomla_username.Mini', {
			required: <?php echo $field->required ? 1 : 0; ?>,
			id: <?php echo $field->id; ?>
		});
	});
