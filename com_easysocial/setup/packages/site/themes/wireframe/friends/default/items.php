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
<div class="es-snackbar2">
	<div class="es-snackbar2__context">
		<div class="es-snackbar2__title">
			<?php if ($filter == 'list') { ?>
				<?php echo $activeList->get('title'); ?>
			<?php } else { ?>

				<?php if ($filter == 'pending') { ?>
					<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_HEADING_PENDING_FRIENDS'); ?>
				<?php } ?>

				<?php if ($filter == 'all') { ?>
					<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_HEADING_ALL_FRIENDS' ); ?>
				<?php } ?>

				<?php if ($filter == 'mutual'){ ?>
					<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_HEADING_MUTUAL_FRIENDS'); ?>
				<?php } ?>

				<?php if ($filter == 'suggest') { ?>
					<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_HEADING_SUGGEST_FRIENDS'); ?>
				<?php } ?>

				<?php if ($filter == 'request') { ?>
					<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_HEADING_FRIENDS_REQUEST_SENT'); ?>
				<?php } ?>

			<?php } ?>
		</div>
	</div>

	<?php if ($activeList && $activeList->user_id == $this->my->id && $filter == 'list') { ?>
	<div class="es-snackbar2__actions">
		<div class="dropdown_" data-list-actions data-id="<?php echo $activeList->id;?>">
			<a href="javascript:void(0);" class="btn btn-sm btn-es-default-o dropdown-toggle_" data-es-toggle="dropdown">
				<?php echo JText::_('COM_EASYSOCIAL_MANAGE_LIST_BUTTON');?>&nbsp; <i class="fa fa-caret-down"></i>
			</a>

			<ul class="dropdown-menu dropdown-menu-right dropdown-menu-lists dropdown-arrow-topright">
				<li>
					<a href="javascript:void(0);" data-add>
						<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_LIST_ADD');?>
					</a>
				</li>
				<?php if ($this->config->get('conversations.enabled')) { ?>
				<li>
					<a href="javascript:void(0);" data-es-conversations-compose data-es-conversations-listid="<?php echo $activeList->id;?>">
						<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_LIST_START_CONVERSATION');?>
					</a>
				</li>
				<?php } ?>

				<li class="divider"></li>
				<li>
					<a href="<?php echo ESR::friends(array('layout' => 'listForm', 'id' => $activeList->id));?>">
						<?php echo JText::_( 'COM_EASYSOCIAL_FRIENDS_LIST_EDIT' );?>
					</a>
				</li>
				<li class="divider">
				</li>
				<li>
					<a href="javascript:void(0);" data-delete>
						<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_LIST_DELETE');?>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<?php } ?>
</div>

<div class="es-list <?php echo !$friends ? 'is-empty' : ''; ?>" data-items>
	<?php if ($friends) { ?>
		<?php foreach ($friends as $friend) { ?>
			<?php echo $this->html('listing.user', $filter == 'suggest' ? $friend->friend : $friend, array('showRemoveFromList' => $filter == 'list')); ?>
		<?php } ?>
	<?php } ?>

	<?php echo $this->html('html.loading'); ?>

	<?php if ($filter == 'pending') { ?>
		<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_FRIENDS_NO_PENDING_APPROVALS', 'fa-users'); ?>
	<?php } ?>

	<?php if ($filter == 'list') { ?>
		<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_FRIENDS_NO_FRIENDS_IN_LIST', 'fa-users'); ?>
	<?php } ?>

	<?php if ($filter == 'suggest') { ?>
		<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_FRIENDS_REQUEST_NO_FRIEND_SUGGESTION', 'fa-users'); ?>
	<?php } ?>

	<?php if ($filter == 'all') { ?>
		<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_FRIENDS_NO_FRIENDS_YET', 'fa-users'); ?>
	<?php } ?>

	<?php if ($filter == 'request') { ?>
		<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_FRIENDS_NO_FRIENDS_REQUEST_SENT', 'fa-users'); ?>
	<?php } ?>

	<?php if ($filter == 'mutual') { ?>
		<?php echo $this->html('html.emptyBlock', ($user->isViewer()) ?  JText::sprintf('COM_EASYSOCIAL_FRIENDS_NO_MUTUAL_FRIENDS_WITH', $user->getName()) : JText::_('COM_EASYSOCIAL_FRIENDS_NO_MUTUAL_FRIENDS'), 'fa-users'); ?>
	<?php } ?>
</div>

<div data-pagination>
	<?php echo $pagination->getListFooter('site');?>
</div>
