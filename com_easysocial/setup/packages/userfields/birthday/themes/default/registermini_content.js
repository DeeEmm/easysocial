EasySocial.require().script('apps/fields/user/datetime/dropdown').done(function($) {

	var element = $('[data-field-<?php echo $field->id; ?>]');

	element.addController('EasySocial.Controller.Field.Datetime.Dropdown', {
		required: <?php echo $field->required ? 1 : 0; ?>,
		yearfrom: <?php echo $yearRange ? $yearRange->min : 'null'; ?>,
		yearto: <?php echo $yearRange ? $yearRange->max : 'null'; ?>,
		allowTime: <?php echo (int) $params->get('allow_time', 0); ?>,
		ageLimit: <?php echo (int) $params->get('age_limit', 0); ?>
	});
});
