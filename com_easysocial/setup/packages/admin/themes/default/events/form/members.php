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
<div data-events-form-guests>
	<div class="panel-table">
		<table class="app-table table">
			<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>

					<th>
						<?php echo $this->html('grid.sort', 'name', JText::_('COM_EASYSOCIAL_USERS_NAME'), $ordering, $direction); ?>
					</th>

					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'state', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATUS'), $ordering, $direction); ?>
					</th>

					<th width="15%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ROLE');?>
					</th>

					<th width="20%" class="center">
						<?php echo $this->html('grid.sort', 'username', JText::_('COM_EASYSOCIAL_USERS_USERNAME'), $ordering, $direction); ?>
					</th>

					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_USERS_ID'), $ordering, $direction); ?>
					</th>
				</tr>
			</thead>

			<tbody>
			<?php if (!empty($guests)) { ?>
				<?php $i = 0; ?>
				<?php foreach ($guests as $guest) { ?>
					<?php $user = ES::user($guest->uid); ?>
					<tr>
						<td><?php echo $this->html('grid.id', $i, $guest->id); ?></td>

						<td style="text-align: left;">
							<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id);?>"
								data-user-insert
								data-id="<?php echo $user->id;?>"
								data-alias="<?php echo $user->getAlias();?>"
								data-title="<?php echo $this->html('string.escape', $user->name);?>"
								data-avatar="<?php echo $this->html('string.escape', $user->getAvatar(SOCIAL_AVATAR_MEDIUM));?>"
							>
								<?php echo $user->name;?>
							</a>
							<?php echo $user->isBanned() || $user->isBlock() ? ' ' . JText::_('COM_ES_CLUSTER_MEMBERS_BANNED') : ''; ?>
						</td>

						<td class="center">
							<?php echo $this->html('grid.published', $guest, 'events', 'state', array('', '', 'approveGuests', '', ''), array('COM_EASYSOCIAL_EVENTS_GUEST_STATE_INVITED', 'COM_EASYSOCIAL_EVENTS_GUEST_STATE_GOING', 'COM_EASYSOCIAL_EVENTS_GUEST_STATE_PENDING', 'COM_EASYSOCIAL_EVENTS_GUEST_STATE_MAYBE', 'COM_EASYSOCIAL_EVENTS_GUEST_STATE_NOT_GOING'), array('invited', 'going', 'pending', 'maybe', 'notgoing')); ?>
						</td>

						<td class="center">
							<?php if ($guest->isOwner()) { ?>
								<span class="o-label o-label--primary-o"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_OWNER'); ?></span>
							<?php } ?>

							<?php if (!$guest->isOwner() && $guest->isAdmin()) { ?>
								<span class="o-label o-label--warning-o"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_ADMIN'); ?></span>
							<?php } ?>

							<?php if (!$guest->isOwner() && !$guest->isAdmin()) { ?>
								<?php $labelStyle = ($user->isBanned() || $user->isBlock()) ? 'o-label--default-o' : 'o-label--success-o'; ?>
								<span class="o-label <?php echo $labelStyle; ?>"><?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUEST_GUEST'); ?></span>
							<?php } ?>
						</td>

						<td class="center">
							<span><?php echo $user->username;?></span>
						</td>

						<td class="center">
							<?php echo $user->id;?>
						</td>
					</tr>
				<?php } ?>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="5">
						<div class="footer-pagination"><?php echo $pagination->getListFooter();?></div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>

<div id="toolbar-members" class="btn-wrapper t-hidden" data-members-dropdown>
	<div class="dropdown">
		<button type="button" class="btn btn-small dropdown-toggle" data-toggle="dropdown">
			<span class="icon-users"></span> <?php echo JText::_('COM_EASYSOCIAL_BUTTON_GUESTS');?> &nbsp;<span class="caret"></span>
		</button>

		<ul class="dropdown-menu">
			<li>
				<a href="javascript:void(0);" data-event-invite-guest>
					<?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUESTS_INVITE_GUEST'); ?>
				</a>
			</li>
			<li>
				<a href="javascript:void(0);" data-event-remove-guest>
					<?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUESTS_REMOVE_GUEST'); ?>
				</a>
			</li>
			<li class="divider">
			</li>
			<li>
				<a href="javascript:void(0);" data-event-approve-guest>
					<?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUESTS_APPROVE_GUEST'); ?>
				</a>
			</li>
			<li class="divider">
			</li>
			<li>
				<a href="javascript:void(0);" data-event-promote-guest>
					<?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUESTS_PROMOTE_TO_ADMIN'); ?>
				</a>
			</li>
			<li>
				<a href="javascript:void(0);" data-event-demote-guest>
					<?php echo JText::_('COM_EASYSOCIAL_EVENTS_GUESTS_REMOVE_ADMIN'); ?>
				</a>
			</li>
		</ul>
	</div>
</div>
