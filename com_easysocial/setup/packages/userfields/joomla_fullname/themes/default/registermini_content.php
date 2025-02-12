<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div data-field-joomla_fullname 
	data-error-empty="<?php echo JText::_('PLG_FIELDS_JOOMLA_FULLNAME_VALIDATION_EMPTY_NAME');?>"
	data-name-format="<?php echo $params->get('format', 1); ?>"
	>
	<div class="o-grid o-grid--gutters">
		<?php if ($params->get('format') != 5) { ?>
			<div class="o-grid__cell">
				<?php echo $this->html('form.floatinglabel', $params->get('format') == 3 ? 'PLG_FIELDS_JOOMLA_FULLNAME_PLACEHOLDER_YOUR_NAME' : 'PLG_FIELDS_JOOMLA_FULLNAME_PLACEHOLDER_FIRST_NAME', 'first_name', 'text', '', 'first_name', true, $params->get('format') == 3 ? 'data-field-jname-name' : 'data-field-jname-first'); ?>
			</div>

			<?php if ($params->get('format') != 3) { ?>
			<div class="o-grid__cell">
				<?php echo $this->html('form.floatinglabel', 'PLG_FIELDS_JOOMLA_FULLNAME_PLACEHOLDER_LAST_NAME', 'last_name', 'text', '', 'last_name', true, 'data-field-jname-last'); ?>
			</div>
			<?php } ?>
		<?php } ?>

		<?php if ($params->get('format') == 5) { ?>
		<div class="o-grid__cell">
			<?php echo $this->html('form.floatinglabel', 'PLG_FIELDS_JOOMLA_FULLNAME_PLACEHOLDER_LAST_NAME', 'last_name', 'text', '', 'last_name', true, 'data-field-jname-last'); ?>
		</div>

		<div class="o-grid__cell">
			<?php echo $this->html('form.floatinglabel', $params->get('format') == 3 ? 'PLG_FIELDS_JOOMLA_FULLNAME_PLACEHOLDER_YOUR_NAME' : 'PLG_FIELDS_JOOMLA_FULLNAME_PLACEHOLDER_FIRST_NAME', 'first_name', 'text', '', 'first_name', true, $params->get('format') == 3 ? 'data-field-jname-name' : 'data-field-jname-first'); ?>
		</div>
		<?php } ?>
	</div>

	<div class="es-fields-error-note" data-field-error></div>
</div>
