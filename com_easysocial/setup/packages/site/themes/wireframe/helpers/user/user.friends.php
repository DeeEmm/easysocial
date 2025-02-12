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
<div class="o-btn-group o-btn-group--es-friends" data-es-friends data-id="<?php echo $user->id;?>">

	<a href="javascript:void(0);" data-es-friends-button class="btn btn-es-default-o btn-<?php echo $size;?> dropdown-toggle_" <?php echo !$isFriends && !$isPending ? ' data-task="add"' : ' data-es-toggle="dropdown"'; ?>>

		<?php if ($isFriends) { ?>
			<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_FRIENDS');?>
		<?php } ?>

		<?php if ($isPending && $isRequester) { ?>
			<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_REQUEST_SENT');?>
		<?php } ?>

		<?php if ($isPending && $isResponder) { ?>
			<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_RESPOND_TO_REQUEST');?>
		<?php } ?>

		<?php if (!$isFriends && !$isPending) { ?>
			<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_ADD_AS_FRIEND');?>
		<?php } ?>

		<?php if ($isFriends || $isPending) { ?>
		<i class="fa fa-caret-down"></i>
		<?php } ?>
	</a>

	<?php if ($isFriends || $isPending) { ?>
	<ul class="dropdown-menu">
	<?php } ?>

	<?php if ($isFriends) { ?>
		<li>
			<a href="javascript:void(0);" data-task="unfriend">
				<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_UNFRIEND');?>
			</a>
		</li>
	<?php } ?>

	<?php if (!$isFriends && $isPending && $isRequester) { ?>
		<li>
			<a href="javascript:void(0);" data-task="cancel">
				<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_CANCEL_FRIEND_REQUEST');?>
			</a>
		</li>
	<?php } ?>

	<?php if (!$isFriends && $isPending && $isResponder) { ?>
		<li>
			<a href="javascript:void(0);" data-task="accept"><?php echo JText::_('COM_EASYSOCIAL_FRIENDS_APPROVE_FRIEND_REQUEST');?></a>
		</li>
		<li>
			<a href="javascript:void(0);" data-task="reject"><?php echo JText::_( 'COM_EASYSOCIAL_FRIENDS_REJECT_FRIEND_REQUEST' );?></a>
		</li>
	<?php } ?>

	<?php if ($isFriends || $isPending) { ?>
	</ul>
	<?php } ?>

</div>
