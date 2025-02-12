<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<dialog>
	<width>600</width>
	<height>350</height>
	<selectors type="json">
	{
		"{doneButton}"		: "[data-done-button]",
		"{cancelButton}" 	: "[data-cancel-button]",
		"{assignForm}"		: "[data-assign-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_BADGES_ASSIGN_BADGE_DIALOG_TITLE' ); ?></title>
	<content>
		
			<p class="t-lg-mt--md t-lg-mb--xl"><?php echo JText::_('COM_EASYSOCIAL_BADGES_ASSIGN_BADGE_MESSAGE'); ?></p>

			<form name="assignBadge" method="post" action="index.php" class="o-form-horizontal" data-assign-form>
				<div class="o-form-group">
					<label class="col-md-3" for="total"><?php echo JText::_('COM_EASYSOCIAL_BADGES_ACHIEVEMENT_DATE'); ?></label>
					<div class="col-md-9">
						<?php echo $this->html('form.calendar', 'achieved', ES::date()->format('d-m-Y')); ?>
					</div>
				</div>

				<div class="o-form-group">
					<label class="col-md-3" for="total"><?php echo JText::_('COM_EASYSOCIAL_BADGES_CUSTOM_MESSAGE'); ?></label>
					<div class="col-md-9">
						<textarea data-badge-message class="o-form-control" name="message" placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_BADGES_ASSIGN_BADGE_CUSTOM_MESSAGE' );?>" style="height: 100px;"></textarea>
					</div>
				</div>

				<input type="hidden" name="option" value="com_easysocial" />
				<input type="hidden" name="controller" value="users" />
				<input type="hidden" name="task" value="insertBadge" />
				<input type="hidden" name="id" value="<?php echo $badge->id;?>" />
				<?php echo $this->html( 'form.token' ); ?>

				<?php foreach( $uids as $uid ){ ?>
				<input type="hidden" name="uid[]" value="<?php echo $uid;?>" />
				<?php } ?>
			</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-done-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_( 'COM_EASYSOCIAL_ASSIGN_BUTTON' ); ?></button>
	</buttons>
</dialog>
