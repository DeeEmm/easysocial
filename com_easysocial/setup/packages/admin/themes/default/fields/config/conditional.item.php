<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($conditions) { ?>
<?php foreach($conditions as $condition) { ?>
<li data-fields-config-param-choice class="t-lg-mb--md" data-id="">
	<div class="o-grid">
		<div class="o-grid__cell t-lg-pr--sm">
			<div class="o-grid">
				<div class="o-grid__cell t-lg-pr--sm t-xs-pr--no t-xs-mb--lg">
					<select id="" name="" class="o-form-control" data-fields-condition-param-choice-field>
						<option value="0"><?php echo JText::_('COM_ES_SELECT_FIELDS') ?></option>
						<?php foreach ($availableFields as $field) { ?>
							<option value="<?php echo $field->id; ?>" <?php echo $condition->fieldId == $field->id ? ' selected="selected"' : ''; ?>><?php echo $field->title; ?></option>
						<?php } ?>
					</select>
				</div>

				<div class="o-grid__cell t-lg-pr--sm t-xs-pr--no t-xs-mb--lg">
					<select id="" name="" class="o-form-control" data-fields-condition-param-choice-operator>
						<option value="equal"<?php echo $condition->operator == 'equal' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_ES_EQUAL'); ?></option>
						<option value="not equal"<?php echo $condition->operator == 'not equal' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_ES_NOT_EQUAL'); ?></option>
						<option value="contain"<?php echo $condition->operator == 'contain' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_ES_CONTAIN'); ?></option>
					</select>
				</div>
				<div class="o-grid__cell">
					<input class="o-form-control" type="text" data-fields-condition-param-choice-value value="<?php echo $condition->value; ?>" placeholder="Value" />
				</div>
			</div>
		</div>
		<div class="o-grid__cell-auto-size">
			<div class="o-btn-group">
				<a href="javascript:void(0);" class="btn btn-es-danger-o"
					data-fields-config-param-choice-remove
					data-original-title="<?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_PARAMS_CHOICES_REMOVE_CHOICE', true); ?>" data-placement="top" data-es-provide="tooltip">
					<i class="fa fa-minus-circle"></i> <?php echo JText::_('COM_ES_REMOVE_CRITERIA'); ?>
				</a>
				<a href="javascript:void(0);" class="btn btn-es-success-o"
					data-fields-config-param-choice-add
					data-original-title="<?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_PARAMS_CHOICES_ADD_CHOICE', true); ?>" data-placement="top" data-es-provide="tooltip">
					<i class="fa fa-plus-circle"></i> <?php echo JText::_('COM_ES_ADD_CRITERIA'); ?>
				</a>
			</div>
		</div>
	</div>
</li>
<?php } ?>
<?php } else { ?>
<li data-fields-config-param-choice class="t-lg-mb--md" data-id="">
	<div class="o-grid">
		<div class="o-grid__cell t-lg-pr--sm">
			<div class="o-grid">
				<div class="o-grid__cell t-lg-pr--sm t-xs-pr--no t-xs-mb--lg">
					<select id="" name="" class="o-form-control" data-fields-condition-param-choice-field>
						<option value="0"><?php echo JText::_('COM_ES_SELECT_FIELDS') ?></option>
						<?php foreach ($availableFields as $field) { ?>
							<option value="<?php echo $field->id; ?>"><?php echo $field->title; ?></option>
						<?php } ?>
					</select>
				</div>

				<div class="o-grid__cell t-lg-pr--sm t-xs-pr--no t-xs-mb--lg">
					<select id="" name="" class="o-form-control" data-fields-condition-param-choice-operator>
						<option value="equal"><?php echo JText::_('COM_ES_EQUAL'); ?></option>
						<option value="not equal"><?php echo JText::_('COM_ES_NOT_EQUAL'); ?></option>
						<option value="contain"><?php echo JText::_('COM_ES_CONTAIN'); ?></option>
					</select>
				</div>
				<div class="o-grid__cell">
					<input class="o-form-control" type="text" data-fields-condition-param-choice-value value="" placeholder="Value" />
				</div>
			</div>
		</div>
		<div class="o-grid__cell-auto-size">
			<div class="o-btn-group">
				<a href="javascript:void(0);" class="btn btn-es-danger-o"
					data-fields-config-param-choice-remove
					data-original-title="<?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_PARAMS_CHOICES_REMOVE_CHOICE', true); ?>" data-placement="top" data-es-provide="tooltip">
					<i class="fa fa-minus-circle"></i> <?php echo JText::_('COM_ES_REMOVE_CRITERIA'); ?>
				</a>
				<a href="javascript:void(0);" class="btn btn-es-success-o"
					data-fields-config-param-choice-add
					data-original-title="<?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_PARAMS_CHOICES_ADD_CHOICE', true); ?>" data-placement="top" data-es-provide="tooltip">
					<i class="fa fa-plus-circle"></i> <?php echo JText::_('COM_ES_ADD_CRITERIA'); ?>
				</a>
			</div>
		</div>
	</div>
</li>
<?php } ?>
