<?php if (!$params->get('use_maps')) { ?>
EasySocial.require().script('apps/fields/user/address/content').done(function($) {
	$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Address', {
		id: <?php echo $field->id; ?>,
		required: <?php echo $required; ?>,
		show: <?php echo $show; ?>,
		selectCountryText: "<?php echo JText::_('PLG_FIELDS_ADDRESS_PLEASE_SELECT_A_COUNTRY_FIRST'); ?>",
		selectStateText: "<?php echo JText::_('PLG_FIELDS_ADDRESS_PLEASE_SELECT_A_STATE'); ?>"
	});
});
<?php } ?>

