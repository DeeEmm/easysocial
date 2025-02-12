<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div data-field-joomla_fullname
	data-name-format="<?php echo $params->get('format', 1); ?>"
	data-max="<?php echo $params->get('max'); ?>"
	data-error-empty="<?php echo JText::_('PLG_FIELDS_JOOMLA_FULLNAME_VALIDATION_EMPTY_NAME');?>">

	<ul class="input-vertical g-list-unstyled">
		<?php if ($params->get('format', 1) == 1 || $params->get('format', 1) == 4){ ?>
		<li>
			<label for="first_name" class="t-hidden">First Name</label>
			<input type="text"
				size="30"
				class="o-form-control"
				id="first_name"
				name="first_name"
				value="<?php echo $firstName; ?>"
				data-field-jname-first
				placeholder="<?php echo JText::_($params->get('firstname_placeholder', 'PLG_FIELDS_JOOMLA_FULLNAME_PLACEHOLDER_FIRST_NAME'), true); ?>"
				<?php if ($field->isRequired()) { ?>data-check-required<?php } ?>
				<?php if ($params->get('regex_validate')) { ?>
				data-check-validate
				data-check-format="<?php echo $params->get('regex_format'); ?>"
				data-check-modifier="<?php echo $params->get('regex_modifier'); ?>"
				<?php } ?>
				/>
		</li>
		<?php } ?>

		<?php if ($params->get('format', 1) == 2 || $params->get('format', 1) == 5){ ?>
		<li>
			<label for="last_name" class="t-hidden">Last Name</label>
			<input type="text"
				size="30"
				class="o-form-control"
				id="last_name"
				name="last_name"
				value="<?php echo $lastName; ?>"
				data-field-jname-last
				placeholder="<?php echo JText::_($params->get('lastname_placeholder', 'PLG_FIELDS_JOOMLA_FULLNAME_PLACEHOLDER_LAST_NAME'), true); ?>"
				<?php if ($params->get('regex_validate')) { ?>
				data-check-validate
				data-check-format="<?php echo $params->get('regex_format'); ?>"
				data-check-modifier="<?php echo $params->get('regex_modifier'); ?>"
				<?php } ?>
				/>
		</li>
		<?php } ?>

		<?php if ($params->get('format', 1) == 3){ ?>
		<li>
			<label for="first_name" class="t-hidden">First Name</label>
			<input type="text"
				size="30"
				class="o-form-control"
				id="first_name"
				name="first_name"
				value="<?php echo $name; ?>"
				data-field-jname-name
				placeholder="<?php echo JText::_($params->get('singlename_placeholder', 'PLG_FIELDS_JOOMLA_FULLNAME_PLACEHOLDER_YOUR_NAME'), true); ?>"
				<?php if ($params->get('regex_validate')) { ?>
				data-check-validate
				data-check-format="<?php echo $params->get('regex_format'); ?>"
				data-check-modifier="<?php echo $params->get('regex_modifier'); ?>"
				<?php } ?>
				/>
		</li>
		<?php } ?>

		<?php if ($params->get('format', 1) < 3){ ?>
		<li>
			<label for="middle_name" class="t-hidden">Middle Name</label>
			<input type="text"
				size="30"
				class="o-form-control"
				id="middle_name"
				name="middle_name"
				value="<?php echo $middleName; ?>"
				data-field-jname-middle
				placeholder="<?php echo JText::_($params->get('middlename_placeholder', 'PLG_FIELDS_JOOMLA_FULLNAME_PLACEHOLDER_MIDDLE_NAME'), true); ?>"
				<?php if ($params->get('regex_validate')) { ?>
				data-check-validate
				data-check-format="<?php echo $params->get('regex_format'); ?>"
				data-check-modifier="<?php echo $params->get('regex_modifier'); ?>"
				<?php } ?>
				/>
		</li>
		<?php } ?>

		<?php if ($params->get('format', 1) == 1 || $params->get('format', 1) == 4){ ?>
		<li>
			<label for="last_name" class="t-hidden">Last Name</label>
			<input type="text"
				size="30"
				class="o-form-control"
				id="last_name"
				name="last_name"
				value="<?php echo $lastName; ?>"
				data-field-jname-last
				placeholder="<?php echo JText::_($params->get('lastname_placeholder', 'PLG_FIELDS_JOOMLA_FULLNAME_PLACEHOLDER_LAST_NAME'), true); ?>"
				<?php if ($params->get('regex_validate')) { ?>
				data-check-validate
				data-check-format="<?php echo $params->get('regex_format'); ?>"
				data-check-modifier="<?php echo $params->get('regex_modifier'); ?>"
				<?php } ?>
				/>
		</li>
		<?php } ?>

		<?php if ($params->get('format', 1) == 2 || $params->get('format', 1) == 5){ ?>
		<li>
			<label for="first_name" class="t-hidden">First Name</label>
			<input type="text"
				size="30"
				class="o-form-control"
				id="first_name"
				name="first_name"
				value="<?php echo $firstName; ?>"
				data-field-jname-first
				placeholder="<?php echo JText::_($params->get('firstname_placeholder', 'PLG_FIELDS_JOOMLA_FULLNAME_PLACEHOLDER_FIRST_NAME'), true); ?>"<?php echo $field->isRequired() ? ' data-check-required' : '';?>
				<?php if ($params->get('regex_validate')) { ?>
				data-check-validate
				data-check-format="<?php echo $params->get('regex_format'); ?>"
				data-check-modifier="<?php echo $params->get('regex_modifier'); ?>"
				<?php } ?>
				/>
		</li>
		<?php } ?>

	</ul>
	<div class="es-fields-error-note" data-field-error></div>
</div>
