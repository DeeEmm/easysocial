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
<div data-field-joomla_username
	data-error-empty="<?php echo JText::_('PLG_FIELDS_JOOMLA_USERNAME_EMPTY_USERNAME', true); ?>"
	data-error-checking="<?php echo JText::_('PLG_FIELDS_JOOMLA_USERNAME_CHECKING', true);?>"
>
	<?php echo $this->html('form.floatinglabel', 'PLG_FIELDS_JOOMLA_USERNAME_USERNAME', 'username', 'text', '', 'joomla_username', true); ?>

	<div class="es-fields-error-note" data-field-error></div>
</div>
