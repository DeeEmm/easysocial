EasySocial.require()
.script('site/marketplaces/create')
.done(function($){

	$('[data-es-marketplaces-create]').implement(EasySocial.Controller.Marketplaces.Create ,{
			"previousLink": "<?php echo FRoute::marketplaces(array('layout' => 'steps' , 'step' => ($currentIndex - 1)) , false);?>",
			"errors": {
				"-601": "<?php echo JText::_('COM_EASYSOCIAL_INVALID_FILE_UPLOADED', true);?>",
				"-600": "<?php echo JText::_('COM_EASYSOCIAL_FILE_SIZE_ERROR', true);?>",
				"noEmptyAllowed": "<?php echo JText::_('COM_ES_STORY_PHOTO_NOTE_MESSAGE');?>"
			}
		}
	);
});
