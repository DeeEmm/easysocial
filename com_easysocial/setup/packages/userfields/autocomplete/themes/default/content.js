EasySocial
.require()
.script('apps/fields/user/autocomplete/content')
.done(function($) {

	$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Autocomplete', {
		required: <?php echo $field->required ? 1 : 0; ?>,
		id: <?php echo $field->id; ?>,
		fieldname: '<?php echo $inputName; ?>',
		exclusion: <?php echo $exclusion ? $exclusion : '[]';?>,
		emptyMessage: "<?php echo JText::_('COM_EASYSOCIAL_FIELDS_AUTOCOMPLETE_NO_ITEMS_FOUND', true);?>"
	});
});
