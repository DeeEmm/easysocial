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
<div class="es-privacy"
	<?php if (!$isHtml) { ?>
	data-es-provide="tooltip" data-placement="top" data-original-title="<?php echo $tooltips; ?>"
	<?php } ?>
	data-mode="<?php echo ($isHtml) ? 'html' : 'ajax'; ?>" <?php echo $item->editable ? ' data-es-privacy-form' : '';?>
>
	<?php if ($item->editable) { ?>
		<a href="javascript:void(0);" class="es-privacy-toggle btn <?php echo $linkStyle == 'button' ? 'btn-es-default-o' : '';?>" data-privacy-toggle>
			<i class="<?php echo $icon; ?>" data-privacy-icon></i>

			<?php if (!$iconOnly) { ?>
			&nbsp; <span data-label><?php echo $defaultLabel;?></span>
			<?php } ?>

			<i class="fa fa-caret-down"></i>
		</a>
	<?php } else { ?>
		<span class="es-privacy-toggle-label">
			<i class="<?php echo $icon; ?>" data-privacy-icon></i>
		</span>
	<?php } ?>

	<?php if( $item->editable ) { ?>

	<ul class="es-privacy-menu dropdown-menu" data-privacy-menu>
		<?php foreach ($item->options as $option) { ?>
			<li class="privacyItem <?php echo $option->active ? 'active':''; ?>" data-item
				data-value="<?php echo $option->key; ?>"
				data-uid="<?php echo $item->uid; ?>" data-type="<?php echo $item->type; ?>"
				data-pid="<?php echo $item->id; ?>"
				<?php echo ($item->override) ? ' data-userid="' . $item->user_id . '"' : ''; ?>
				data-pitemid="<?php echo $item->pid; ?>"
				data-streamid="<?php echo $streamid; ?>"
				data-privacy-icon="<?php echo $option->icon;?>"
			>
				<a href="javascript:void(0);">
					<i class="<?php echo $option->icon; ?>"></i>&nbsp; <span data-label><?php echo $option->label;?></span>
				</a>
			</li>
		<?php } ?>
	</ul>

	<div class="es-privacy-menu es-privacy-custom-form dropdown-menu dropdown-menu-right" data-privacy-custom-form>
		<div class=""><?php echo ($this->config->get('friends.enabled')) ? JText::_('COM_EASYSOCIAL_PRIVACY_CUSTOM_DIALOG_NAME') : JText::_('COM_EASYSOCIAL_PRIVACY_CUSTOM_DIALOG_NAME_NONFRIEND'); ?></div>
		<div>
			<div class="textboxlist" data-textfield>
				<?php if (count($item->custom) > 0) { ?>
					<?php foreach ($item->custom as $friend) { ?>
						<?php $friend = ES::user($friend->user_id); ?>
					<div class="textboxlist-item" data-id="<?php echo $friend->id; ?>" data-title="<?php echo $friend->getName(); ?>" data-textboxlist-item>
						<span class="textboxlist-itemContent" data-textboxlist-itemContent><?php echo $friend->getName(); ?><input type="hidden" name="items" value="<?php echo $friend->id; ?>" /></span>
						<a class="textboxlist-itemRemoveButton" href="javascript: void(0);" data-textboxlist-itemRemoveButton></a>
					</div>
					<?php } ?>
				<?php } ?>
				<input type="text" class="textboxlist-textField" data-textboxlist-textField placeholder="<?php echo JText::_('COM_EASYSOCIAL_PRIVACY_CUSTOM_DIALOG_ENTER_NAME'); ?>" autocomplete="off" />
			</div>
		</div>

		<div class="t-lg-pt--md">
			<span class="t-text--warning t-hidden" data-privacy-custom-notice><?php echo JText::_('COM_EASYSOCIAL_PRIVACY_CUSTOM_EMPTY_NAMR_NOTICE');?></span>
			<button data-cancel-button type="button" class="btn btn-es-default-o btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
			<button data-save-button type="button" class="btn btn-es-primary-o btn-sm"><?php echo JText::_('COM_EASYSOCIAL_SAVE_BUTTON'); ?></button>
		</div>
	</div>

	<div class="es-privacy-menu es-privacy-field-form dropdown-menu dropdown-menu-right" data-privacy-field-form>
		<div class="pb-5"><?php echo JText::_('COM_EASYSOCIAL_PRIVACY_CUSTOM_DIALOG_CUSTOM_FIELD'); ?></div>
		<div>
			<?php if ($item->field) { ?>
				<?php foreach ($item->field as $field) { ?>
				<?php
					$options = $field->options;
				?>
				<div class="o-form-group">
					<label><?php echo JText::_($field->title); ?></label>
					<div class="o-select-group">
						<select name="<?php echo $field->element . '|' . $field->unique_key; ?>" class="o-form-control" style="min-height: 80px;" data-privacy-field-inputs multiple="multiple">
						<?php foreach ($options as $option) { ?>
							<option value="<?php echo $option->value; ?>" <?php echo ($option->selected) ? 'selected="selected"' : ''; ?>><?php echo JText::_($option->title); ?></option>
						<?php } ?>
						</select>
					</div>
				</div>
				<?php } ?>
			<?php } ?>
		</div>

		<div class="t-lg-pt--md">
			<span class="t-text--warning t-hidden" data-privacy-field-notice><?php echo JText::_('COM_EASYSOCIAL_PRIVACY_FIELD_EMPTY_NOTICE');?></span>
			<button data-cancel-button type="button" class="btn btn-es-default-o"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
			<button data-save-button type="button" class="btn btn-es-primary-o"><?php echo JText::_('COM_EASYSOCIAL_SAVE_BUTTON'); ?></button>
		</div>
	</div>

	<?php } ?>

	<input type="hidden" name="privacy" value="<?php echo $defaultKey; ?>" data-privacy-hidden />
	<input type="hidden" name="privacyCustom" value="<?php echo $defaultCustom; ?>" data-privacy-custom-hidden />
	<input type="hidden" name="privacyField" value="" data-privacy-field-hidden />
</div>
