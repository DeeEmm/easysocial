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

<?php if ($page->isInvited() && !$page->isMember()) { ?>
<a class="btn btn-es-success-o btn-sm" href="javascript:void(0);" data-es-pages-respond-invitation data-id="<?php echo $page->id;?>" data-page-reload="<?php echo $forceReload; ?>">
	<i class="fa fa-flash mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_GROUPS_RESPOND_TO_INVITATION');?>
</a>
<?php } ?>

<?php if ($page->showLikeButton()) { ?>
<a class="btn btn-es-default-o btn-sm" href="javascript:void(0);" data-es-pages-like data-id="<?php echo $page->id;?>" data-page-reload="<?php echo $forceReload; ?>">
	<?php echo JText::_('COM_EASYSOCIAL_PAGES_LIKE_PAGE');?>
</a>
<?php } ?>

<?php if ($page->isMember()) { ?>
<a class="btn btn-es-default-o btn-sm" href="javascript:void(0);" data-es-pages-unlike data-id="<?php echo $page->id;?>" data-return="<?php echo $returnUrl;?>">
	<?php echo JText::_('COM_EASYSOCIAL_PAGES_UNLIKE_PAGE');?>
</a>
<?php } ?>

<?php if (!$page->isMember() && $page->isPendingMember()) { ?>
<div data-request-sent class="o-btn-group">
	<button type="button" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown">
		<?php echo JText::_('COM_EASYSOCIAL_REQUEST_SENT');?> &nbsp;<i class="fa fa-caret-down"></i>
	</button>
	<ul class="dropdown-menu">
		<li data-es-pages-withdraw data-id="<?php echo $page->id;?>">
			<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_PAGES_WITHDRAW_REQUEST'); ?></a>
		</li>
	</ul>
</div>
<?php } ?>
