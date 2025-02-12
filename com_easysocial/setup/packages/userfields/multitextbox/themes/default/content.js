
EasySocial
	.require()
	.script('apps/fields/user/multitextbox/content')
	.done(function($) {
		$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Multitextbox', {
			required: <?php echo $field->required ? 1 : 0; ?>,
			id: '<?php echo $field->id; ?>',
			inputName: '<?php echo $inputName; ?>'
		});
	});
