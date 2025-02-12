<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<a data-es-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle_ btn-convo-dropdown" href="javascript:void(0);">
	<i class="fa fa-ellipsis-h"></i>
</a>
<ul class="dropdown-menu dropdown-menu-right">

	<?php if ($conversation && $conversation->isReadable($this->my->id)) { ?>
		<li>
			<a href="javascript:void(0);" data-es-viewparticipants>
				<?php echo JText::_('COM_ES_CONVERSATION_VIEW_PARTICIPANT');?>
			</a>
		</li>
		<?php if ($conversation && $conversation->isWritable($this->my->id) && $this->access->allowed('conversations.invite') && !$conversation->isArchived($this->my->id)) { ?>
			<li>
				<a href="javascript:void(0);" data-es-addparticipant>
					<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_ADD_PARTICIPANT');?>
				</a>
			</li>

		<?php } ?>

		<li class="divider"></li>
		<?php if ($conversation && !$conversation->isArchived($this->my->id) && $conversation->isWritable()) { ?>

			<?php if ($this->isMobile()) { ?>
			<li>
				<a href="javascript:void(0);" data-es-rename>
					<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_EDIT_TITLE');?>
				</a>
			</li>
			<?php } ?>
		<li>
			<a href="javascript:void(0);" data-es-unread>
				<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_MARK_UNREAD'); ?>
			</a>
		</li>
		<?php } ?>

		<?php if ($conversation && $conversation->isArchived($this->my->id)) { ?>
			<li>
				<a href="javascript:void(0);" data-es-unarchive>
					<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_UNARCHIVE'); ?>
				</a>
			</li>
		<?php } else { ?>
			<li>
				<a href="javascript:void(0);" data-es-archive>
					<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_ARCHIVE'); ?>
				</a>
			</li>
		<?php } ?>

		<?php if ($conversation && $conversation->isMultiple() && $conversation->canLeave()) { ?>
			<li class="divider"></li>
			<li>
				<a href="javascript:void(0);" data-es-leave>
					<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_LEAVE'); ?>
				</a>
			</li>
		<?php } ?>

		<li class="divider"></li>
	<?php } ?>

	<li>
		<a href="javascript:void(0);" data-es-delete>
			<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_DELETE');?>
		</a>
	</li>
</ul>
