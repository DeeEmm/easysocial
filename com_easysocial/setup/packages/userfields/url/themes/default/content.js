EasySocial
.require()
.script('apps/fields/user/url/content')
.done(function($) {
	$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Url', {
		required: <?php echo $field->required ? 1 : 0; ?>
	});
});