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
<li data-fields-config-param-choice class="t-lg-mb--md" data-id="<?php echo $id; ?>">
	<div class="o-grid">
		<div class="o-grid__cell-auto-size t-lg-pr--sm">
			<a href="javascript:void(0);" class="fields-config-param-choice"
				data-fields-config-param-choice-drag data-original-title="<?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_PARAMS_CHOICES_DRAG_CHOICE', true); ?>" data-placement="top" data-es-provide="tooltip"
			>
				<i class="fa fa-bars"></i>
			</a>
		</div>

		<div class="o-grid__cell t-lg-pr--sm">
			<div class="o-grid">
				<div class="o-grid__cell t-lg-pr--sm t-xs-pr--no t-xs-mb--lg">
					<input class="o-form-control" type="text" data-fields-config-param-choice-title value="<?php echo $title; ?>" placeholder="<?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_PARAMS_CHOICES_TITLE', true); ?>" />
				</div>

				<div class="o-grid__cell">
					<input class="o-form-control" type="text" data-fields-config-param-choice-value value="<?php echo $value; ?>" placeholder="<?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_PARAMS_CHOICES_VALUE', true); ?>" />
				</div>
			</div>
			<input type="hidden" data-fields-config-param-choice-default value="<?php echo $default; ?>" />
		</div>

		<div class="o-grid__cell-auto-size">
			<?php if ($hasDefault) { ?>
			<div class="o-btn-group">
				<a href="javascript:void(0);" class="btn btn-es-default-o"
					data-fields-config-param-choice-setdefault data-original-title="<?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_PARAMS_CHOICES_TOGGLE_DEFAULT_CHOICE', true); ?>" data-placement="top" data-es-provide="tooltip">
					<i data-fields-config-param-choice-defaulticon class="fa fa-star es-state-<?php echo $default ? 'featured' : 'default'; ?>"></i>
				</a>
			</div>
			<?php } ?>

			<div class="o-btn-group">
				<a href="javascript:void(0);" class="btn btn-es-danger-o"
					data-fields-config-param-choice-remove
					data-original-title="<?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_PARAMS_CHOICES_REMOVE_CHOICE', true); ?>" data-placement="top" data-es-provide="tooltip">
					<i class="fa fa-minus-circle"></i>
				</a>
				<a href="javascript:void(0);" class="btn btn-es-success-o"
					data-fields-config-param-choice-add
					data-original-title="<?php echo JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_PARAMS_CHOICES_ADD_CHOICE', true); ?>" data-placement="top" data-es-provide="tooltip">
					<i class="fa fa-plus-circle"></i>
				</a>

			</div>
		</div>
	</div>
</li>
