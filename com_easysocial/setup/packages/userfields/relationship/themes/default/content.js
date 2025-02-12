
// To pass field independent data in through php
EasySocial.module('field.relationship/<?php echo $field->id; ?>', function($) {
	this.resolve(<?php echo json_encode($types); ?>);
});

EasySocial
	.require()
	.script('apps/fields/user/relationship/content')
	.done(function($) {

		$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Relationship', {
			required: <?php echo $field->required ? 1 : 0; ?>,
			id: <?php echo $field->id; ?>,
			fieldname: '<?php echo $inputName; ?>',
			userid: <?php echo $user->id; ?>
		});
	});
