
EasySocial
	.require()
	.script('apps/fields/user/multidropdown/content')
	.done(function($) {
		$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Multidropdown', {
			required: <?php echo $field->required ? 1 : 0; ?>,
			id: '<?php echo $field->id; ?>',
			inputName: '<?php echo $inputName; ?>'
		});
	});
