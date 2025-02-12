EasySocial
	.require()
	.script('apps/fields/user/joomla_fullname/content')
	.done(function($) {
		$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Joomla_fullname', {
			required: <?php echo $field->required ? 1 : 0; ?>,
			registerMini: true,
			id: <?php echo $field->id; ?>
		});
	});
