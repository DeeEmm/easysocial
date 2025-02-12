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
<dialog>
	<width>500</width>
	<height>300</height>
	<selectors type="json">
	{
		"{doneButton}"		: "[data-done-button]",
		"{cancelButton}" 	: "[data-cancel-button]",
		"{form}"			: "[data-assign-points-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function()
		{
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_POINTS_ASSIGN_POINTS_DIALOG_TITLE'); ?></title>
	<content>
		<form name="assignPoints" method="post" action="index.php" data-assign-points-form class="o-form-horizontal">
			<p><?php echo JText::_('COM_EASYSOCIAL_POINTS_ASSIGN_POINTS_MESSAGE'); ?></p>

			<div class="o-form-group">
				<label class="o-control-label" for="total"><?php echo JText::_('COM_EASYSOCIAL_POINTS' ); ?></label>
				<div class="o-control-input">
					<div class="o-form-inline">
						<input type="text" name="points" class="o-form-control" value="" /> <?php echo JText::_('COM_EASYSOCIAL_POINTS'); ?>
					</div>

				</div>
			</div>

			<div class="o-form-group">
				<label class="o-control-label" for="total"><?php echo JText::_('COM_EASYSOCIAL_POINTS_CUSTOM_MESSAGE'); ?></label>
				<div class="o-control-input">
					<textarea name="message" class="o-form-control" placeholder="<?php echo JText::_('COM_EASYSOCIAL_POINTS_ASSIGN_POINTS_CUSTOM_MESSAGE');?>" style="height: 100px;"></textarea>
				</div>
			</div>

			<div class="o-form-group">
				<label class="o-control-label" for="total"><?php echo JText::_('Rule'); ?> (Optional)</label>
				<div class="o-control-input">
					<?php if ($rules) { ?>
					<select class="o-form-control" name="command">
						<option value="com_easysocial|custom">Custom Rule</option>
						<?php foreach ($rules as $rule) { ?>
						<option value="<?php echo $rule->extension;?>|<?php echo $rule->command;?>"><?php echo $rule->command;?> (<?php echo $rule->extension;?>)</option>
						<?php } ?>
					</select>
					<?php } ?>
				</div>
			</div>

			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="users" />
			<input type="hidden" name="task" value="insertPoints" />
			<?php echo $this->html( 'form.token' ); ?>

			<?php foreach( $uids as $uid ){ ?>
			<input type="hidden" name="uid[]" value="<?php echo $uid;?>" />
			<?php } ?>

		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-done-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_ASSIGN_BUTTON'); ?></button>
	</buttons>
</dialog>
