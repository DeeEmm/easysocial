<?php
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
EasySocial
	.require()
	.script('apps/fields/marketplace/image/content')
	.done(function($) {

		$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Image', {
			required: 1,
			id: <?php echo $field->id; ?>,
			inputName: "<?php echo $inputName; ?>",
			uploader: {
				settings: {
					url: "<?php echo JRoute::_('index.php') . '?option=com_easysocial&controller=marketplaces&task=uploadPhotos&inputName=' . $inputName . '&format=json&tmpl=component&' . ES::token() . '=1'; ?>",
					camera: "image",
					max_file_size: "<?php echo $maxFileSize; ?>"
				}
			},
			"errors": {
				"-601": "<?php echo JText::_('COM_EASYSOCIAL_INVALID_FILE_UPLOADED', true);?>",
				"-600": "<?php echo JText::_('COM_EASYSOCIAL_FILE_SIZE_ERROR', true);?>",
				"noEmptyAllowed": "<?php echo JText::_('COM_ES_MARKETPLACE_PHOTO_NOTE_MESSAGE');?>"
			}
		});
	});
