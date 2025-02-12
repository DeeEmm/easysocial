<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="data-field-multitextbox" data-field-multitextbox data-max="<?php echo $params->get('max'); ?>"
	data-error-required="<?php echo JText::_('PLG_FIELDS_MULTITEXTBOX_VALIDATION_REQUIRED_FIELD', true);?>"
>
	<ul class="g-list-unstyled" data-field-multitextbox-list>
		<?php if (!empty($value)) { ?>
			<?php foreach ($value as $v) { ?>
				<?php echo $class->loadTemplate('input', array('inputName' => $inputName, 'value' => ES::string()->escape($v), 'placeholder' => $params->get('placeholder'))); ?>
			<?php } ?>
		<?php } ?>

		<?php if (!$count) { ?>
			<?php echo $class->loadTemplate('input', array('inputName' => $inputName, 'value' => '', 'placeholder' => $params->get('placeholder'))); ?>
		<?php } ?>
	</ul>


	<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm t-lg-mt--lg" data-field-multitextbox-add <?php if (!(empty($limit) || $count < ($limit - 1))) { ?>style="display: none;"<?php } ?>>
		<?php echo JText::_($params->get('add_button_text')); ?>
	</a>
</div>


