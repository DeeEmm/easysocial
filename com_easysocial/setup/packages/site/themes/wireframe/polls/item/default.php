<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-stream-embed is-polls" data-polls-item data-id="<?php echo $poll->id;?>" data-element="<?php echo $poll->element;?>" data-uid="<?php echo $poll->uid;?>">
	<form id="pollsForm" name="pollsForm" class="o-form-horizontal">

		<div class="es-polls__title">
			<?php echo ES::string()->escape($poll->_('title')); ?>
		</div>

		<?php if ($expired) { ?>
		<div class="t-fs--sm"><?php echo JText::_('COM_EASYSOCIAL_POLLS_VOTE_EXPIRED'); ?></div>
		<?php } ?>

		<?php if ($poll->hasExpirationDate() && !$expired) { ?>
		<div class="t-fs--sm">
			<?php echo JText::sprintf('COM_EASYSOCIAL_POLLS_WILL_EXPIRE', $poll->getExpiryDate()->format(JText::_('DATE_FORMAT_LC1'))); ?>
		</div>
		<?php } ?>

		<div class="es-polls__list <?php echo $expired ? 'is-disabled' : '';?>">
			<?php if ($options) { ?>
				<?php foreach ($options as $option) { ?>
				<div class="es-polls__item o-checkbox" data-option data-count="<?php echo $option->count;?>" data-id="<?php echo $option->id;?>">

					<input type="checkbox" id="poll-option-<?php echo $option->id;?>" name="optionsRadios" <?php echo $option->voted && $option->user_state ? 'checked="checked"' : '';?> <?php echo $expired ? 'disabled="disabled"' : '';?> data-checkbox />

					<label for="poll-option-<?php echo $option->id;?>">
						<div>
							<?php echo ES::string()->escape($option->value);?>
						</div>

						<div class="es-polls__progress progress">
							<div class="progress-bar progress-bar-primary" style="width: <?php echo $option->percentage;?>%;" data-progress></div>
						</div>

						<div class="es-polls__voters t-hidden" data-voters></div>

						<a href="javascript:void(0);" class="es-polls__count <?php echo $this->my->guest ? 'disabled' : ''; ?>" data-view-voters>
							<span data-counter><?php echo $option->count;?></span> <?php echo JText::_('COM_EASYSOCIAL_POLLS_VOTES_COUNT');?>
						</a>
					</label>
				</div>
				<?php } ?>
			<?php } ?>
		</div>


	</form>

	<div class="t-text--right">
		<?php if ($poll->isMultiple()) { ?>
			<i class="fa fa-list-ul"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_POLLS_MULTIPLE_CHOICES'); ?>
		<?php } else { ?>
			<i class="fa fa-list-ol"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_POLLS_SINGLE_CHOICE'); ?>
		<?php } ?>
	</div>
</div>
