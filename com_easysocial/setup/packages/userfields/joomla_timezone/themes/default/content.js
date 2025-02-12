
EasySocial
.require()
.script('apps/fields/user/joomla_timezone/content')
.done(function($) {
	$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Joomla_timezone', {
		required: <?php echo $field->required ? 1 : 0; ?>
	});
});
