<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php echo (isset($advancedsearchlink) && $advancedsearchlink) ? '<a href="' . $advancedsearchlink . '">' : ''; ?>
<?php echo $date; ?>
<?php echo (isset($advancedsearchlink) && $advancedsearchlink) ? '</a>' : ''; ?>

<?php if (!empty($age)) { ?> (<?php echo JText::sprintf('FIELDS_USER_BIRTHDAY_YEARS_OLD', $age); ?>)<?php } ?>
<?php if ($allowYearSettings) { ?>
	<div class="data-field-datetime-yearprivacy t-lg-mt--md">
		<div class="t-pull-right">
			<?php echo ES::privacy()->form($field->id, 'birthday.year', $this->my->id, 'field.birthday.year');?>
		</div>
		<h4 class="es-title"><?php echo JText::_('PLG_FIELDS_BIRTHDAY_YEAR_PRIVACY_TITLE'); ?></h4>
		<div>
			<?php echo JText::_('PLG_FIELDS_BIRTHDAY_YEAR_PRIVACY_INFO'); ?>

		</div>
	</div>
<?php } ?>
