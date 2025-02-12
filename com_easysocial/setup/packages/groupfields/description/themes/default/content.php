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
<div class="data-field-textarea" data-field-description data-min="<?php echo $params->get('min'); ?>" data-max="<?php echo $params->get('max'); ?>"
	data-error-required="<?php echo JText::_('PLG_FIELDS_GROUP_DESCRIPTION_VALIDATION_INPUT_REQUIRED', true);?>"
>
    <?php echo $editor->display($inputName, $value, '100%', '350', '10', '10', false, null, 'com_easysocial'); ?>
</div>


