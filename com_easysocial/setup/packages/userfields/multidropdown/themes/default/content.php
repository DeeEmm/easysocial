<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="data-field-multitextbox" data-field-multidropdown data-max="<?php echo $params->get('max'); ?>" data-error="<?php echo JText::_('PLG_FIELDS_MULTIDROPDOWN_VALIDATION_REQUIRED_FIELD', true);?>">
	<ul class="g-list-unstyled" data-field-multidropdown-list>
		
		<?php if ($selected) { ?>
			<?php foreach ($selected as $value) { ?>
				<?php echo $class->loadTemplate('input', array('inputName' => $inputName, 'choices' => $choices, 'value' => $value, 'showDefault' => $showDefault)); ?>
			<?php } ?>
		<?php }  ?>

		<?php if (!$count) { ?>
			<?php echo $class->loadTemplate('input', array('inputName' => $inputName, 'choices' => $choices, 'value' => '', 'showDefault' => $showDefault));?>
		<?php } ?>
	</ul>
</div>
