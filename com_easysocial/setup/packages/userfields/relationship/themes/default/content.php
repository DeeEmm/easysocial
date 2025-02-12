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
<div class="data-field-relationship"
	data-field-relationship
	data-error-required="<?php echo JText::_('PLG_FIELDS_JOOMLA_EMAIL_VALIDATION_REQUIRED', true);?>"
	data-error-target="<?php echo JText::_('PLG_FIELDS_JOOMLA_EMAIL_VALIDATION_TARGET_REQUIRED', true);?>">

	<div class="o-select-group">
		<select class="o-form-control t-lg-mb--md" id="<?php echo $inputName; ?>_type" name="<?php echo $inputName; ?>[type]" data-rs-type  <?php echo ($relation && $relation->type != 'na') || $requests ? ' disabled="disabled"' : '';?>>
			<?php foreach ($types as $type) { ?>
			<option value="<?php echo $type->value; ?>" <?php echo $type->selected ? 'selected="selected"' : '';?> data-connection="<?php echo $type->connect;?>">
				<?php echo $type->label;?>
			</option>
			<?php } ?>
		</select>

		<label for="<?php echo $inputName; ?>_type" class="o-select-group__drop"></label>
	</div>

	<div data-rs-input <?php echo !$relation || ($relation && !$relation->isConnect()) || $relation->isPending() || $relation->isApproved() ? 'style="display:none;"' : '';?>>
		<div class="o-form-control">
			<?php if ($relation && $relation->isConnect() && !empty($relation->target)) { ?>
			<div class="textboxlist-item" data-textboxlist-item data-id="<?php echo $relation->getTargetUser()->id; ?>">
				<span class="textboxlist-itemContent" data-textboxlist-itemcontent>
					<?php echo $relation->getTargetUser()->getName(); ?>
					<input type="hidden" name="<?php echo $inputName; ?>[target][]" value="<?php echo $relation->getTargetUser()->id; ?>">
				</span>
				<div class="textboxlist-itemRemoveButton" data-textboxlist-itemremovebutton="">×</div>
			</div>
			<?php } ?>
			<input data-relationship-form-input-field type="text" class="textboxlist-textField o-form-control" data-textboxlist-textField placeholder="<?php echo JText::_('APP_RELATIONSHIP_USER_INPUT_PLACEHOLDER'); ?>" />
		</div>
	</div>

	<div class="o-box" data-rs-target <?php echo !$relation || !$relation->target || $relation->isApproved() ? 'style="display:none;"' : '';?>>
		<div class="o-flag">
			<div class="o-flag__image o-flag--top">
				<img width="24" height="24" data-rs-target-avatar src="<?php echo !empty($relation->target) ? $relation->getTargetUser()->getAvatar(SOCIAL_AVATAR_MEDIUM) : ''; ?>" />
			</div>
			<div class="o-flag__body">
				<a href="javascript: void(0);" class="btn btn-es-default-o btn-xs t-lg-pull-right" data-rs-form-delete <?php echo !empty($relation->target) ? 'data-id="' . $relation->getTargetUser()->id . '"' : '';?>>
					<i class="fa fa-times"></i>
				</a>

				<div style="font-weight:700;" data-rs-target-name>
					<?php echo !empty($relation->target) ? $relation->getTargetUser()->getName() : ''; ?>
				</div>

				<div class="t-fs--sm" data-relationship-form-target-pending>
					<?php echo JText::_('PLG_FIELDS_RELATIONSHIP_REQUIRES_APPROVAL_INFO'); ?>
				</div>


			</div>
		</div>
	</div>

	<?php if ($relation && $relation->isApproved() && $relation->type != 'na') { ?>
		<?php echo $this->loadTemplate('themes:/fields/user/relationship/relation', array('relation' => $relation, 'targetUser' => $targetUser, 'inputName' => $inputName)); ?>
	<?php } ?>

	<?php if ($requests) { ?>
		<?php foreach ($requests as $request) { ?>
		<div class="o-box t-lg-mt--xl" data-rs-request data-id="<?php echo $request->id; ?>">
			<div class="o-flag">
				<div class="o-flag__image o-flag--top">
					<?php echo $this->html('avatar.user', $request->getActorUser()); ?>
				</div>

				<div class="o-flag__body">
					<div class="t-lg-mb--xl">
						<?php echo JText::sprintf('PLG_FIELDS_RELATIONSHIP_REQUEST_MESSAGE', $request->getActorUser()->getName()); ?>
					</div>
					<div>
						<button class="btn btn-es-danger-o btn-sm" type="button" data-rs-reject data-id="<?php echo $request->id; ?>">
							<?php echo JText::_('PLG_FIELDS_RELATIONSHIP_ACTION_REJECT'); ?>
						</button>
						<button class="btn btn-es-primary-o btn-sm" type="button" data-rs-approve data-id="<?php echo $request->id; ?>">
							<?php echo JText::_('PLG_FIELDS_RELATIONSHIP_ACTION_APPROVE'); ?>
						</button>
					</div>

					<div class="t-lg-mt--md">
						<?php echo JText::_('PLG_FIELDS_RELATIONSHIP_REQUEST_NOTE'); ?>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
		<input type="hidden" name="<?php echo $inputName; ?>[typeRelation]" value="na" data-rs-request-hidden>
	<?php } ?>

	<div class="es-fields-error-note" data-field-error><?php echo $error; ?></div>
</div>
