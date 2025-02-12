
EasySocial
	.require()
	.script('apps/fields/user/joomla_email/content')
	.done(function($) {
		$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Joomla_email', {
			required: <?php echo $field->required ? 1 : 0; ?>,
			id: <?php echo $field->id; ?>,
			userid: <?php echo $userid ? $userid : 0; ?>,
			reconfirm: <?php echo $showConfirmation ? 'true' : 'false'; ?>,
			event: '<?php echo $event; ?>',
			registration: <?php echo isset($registration) && $registration ? 'true' : 'false'; ?>
		});
	});
