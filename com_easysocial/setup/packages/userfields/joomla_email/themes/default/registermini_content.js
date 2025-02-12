
EasySocial
	.require()
	.script('apps/fields/user/joomla_email/registermini_content')
	.done(function($) {
		$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Joomla_email.Mini', {
			required: <?php echo $field->required ? 1 : 0; ?>,
			id: <?php echo $field->id; ?>,
            reconfirm: <?php echo $showConfirmation ? 'true' : 'false'; ?>,
		});
	});
