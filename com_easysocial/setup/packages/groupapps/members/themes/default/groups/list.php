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
<?php if ($filterType == 'pending' && $pending) { ?>
	<div class="es-list-item-actions">
		<div class="o-grid-sm o-grid-sm--center">
			<div class="o-grid-sm__cell">
				<div class="o-checkbox t-xs-ml--lg">
					<input type="checkbox" id="es-media-select-all-checkbox" data-group-item-checkall>
					<label for="es-media-select-all-checkbox">
							<?php echo JText::_('COM_ES_SELECT_ALL'); ?>
					</label>
				</div>
			</div>
			<div class="o-grid-sm__cell o-grid-sm__cell--right">
				<div class="t-hidden" data-group-actions-wrapper>
					<div class="o-select-group o-select-group--inline">
						<select class="o-form-control" data-group-actions-task>
							<option><?php echo JText::_('COM_ES_BULK_ACTIONS'); ?></option>
							<?php ?>
							<option value="site/controllers/groups/approve"
									data-confirmation="site/views/groups/confirmApprove"
									>
									<?php echo JText::_('COM_ES_BULK_ACTIONS_APPROVE'); ?>
							</option>
							<?php  ?>
							<?php ?>
							<option value="site/controllers/groups/reject"
									data-confirmation="site/views/groups/confirmReject"
									>
									<?php echo JText::_('COM_ES_BULK_ACTIONS_REJECT'); ?>
							</option>
							<?php  ?>
						</select>
						<label for="" class="o-select-group__drop"></label>
					</div>

					<a href="javascript:void(0);" class="btn btn-es-primary-o" data-group-actions-apply><?php echo JText::_('COM_ES_APPLY'); ?></a>
				</div>
			</div>
		</div>
	</div>
<?php } ?>
<div class="es-list" data-result>
<?php if ($users) { ?>
	<?php foreach ($users as $user) { ?>
	<div class="es-list__item">
		<div class="es-list-item es-island" data-item data-id="<?php echo $user->id;?>" data-return="<?php echo $returnUrl;?>">
			<?php if ($filterType == 'pending') { ?>
				<div class="es-list-item__checkbox">
					<div class="o-checkbox">
						<input type="checkbox" id="es-group-member-select-checkbox-<?php echo $user->id;?>" value="<?php echo $user->id; ?>" data-group-item-checkbox>
						<label for="es-group-member-select-checkbox-<?php echo $user->id;?>">&nbsp;</label>
					</div>
				</div>
			<?php } ?>

			<div class="es-list-item__media">
				<?php echo $this->html('avatar.user', $user, 'md', true); ?>
			</div>

			<div class="es-list-item__context">
				<div class="es-list-item__hd">
					<div class="es-list-item__content">

						<div class="es-list-item__title">
							<?php echo $this->html('html.user', $user); ?>
						</div>

						<div class="es-list-item__meta">
							<ol class="g-list-inline g-list-inline--delimited">
								<li data-breadcrumb="&#183;">
									<?php if ($group->isOwner($user->id)) { ?>
									<span class="o-label o-label--danger-o"><?php echo JText::_('APP_GROUP_MEMBERS_OWNER'); ?></span>
									<?php } ?>

									<?php if ($group->isAdmin($user->id) && !$group->isOwner($user->id)) { ?>
									<span class="o-label o-label--success-o"><?php echo JText::_('APP_GROUP_MEMBERS_ADMIN'); ?></span>
									<?php } ?>

									<?php if (!$group->isAdmin($user->id) && $user->isSiteAdmin()) { ?>
									<span class="o-label o-label--info-o"><?php echo JText::_('APP_GROUP_MEMBERS_MODERATOR'); ?></span>
									<?php } ?>

									<?php if ($group->isMember($user->id) && !$group->isAdmin($user->id) && !$group->isOwner($user->id) && !$user->isSiteAdmin()) { ?>
									<span class="o-label o-label--clean-o"><?php echo JText::_('APP_GROUP_MEMBERS_MEMBER'); ?></span>
									<?php } ?>

									<?php if ($group->isPendingMember($user->id)) { ?>
									<span class="o-label o-label--warning-o label-pending"><?php echo JText::_('APP_GROUP_MEMBERS_PENDING');?></span>
									<?php } ?>

									<?php if ($group->isPendingInvitationApproval($user->id)) { ?>
									<span class="o-label o-label--default-o label-pending-invitation"><?php echo JText::_('APP_GROUP_MEMBERS_INVITED');?></span>
									<?php } ?>
								</li>

								<?php if ($group->isPendingInvitationApproval($user->id)) { ?>
								<li data-breadcrumb="&#183;">
									<?php echo JText::sprintf('APP_GROUP_MEMBERS_INVITED_BY', $this->html('html.user', $group->getInvitor($user->id)->id, true), $group->getJoinedDate($user->id, SOCIAL_TYPE_USER, true)); ?>
								</li>
								<?php } ?>

								<?php if ($group->isMember($user->id) && !$group->isInvited($user->id)) { ?>
								<li data-breadcrumb="&#183;">
									<?php echo JText::sprintf('APP_GROUP_MEMBERS_JOINED', $group->getJoinedDate($user->id, SOCIAL_TYPE_USER, true)); ?>
								</li>
								<?php } ?>

								<?php if ($group->isPendingMember($user->id)) { ?>
								<li data-breadcrumb="&#183;">
									<?php echo JText::sprintf('APP_GROUP_MEMBERS_REQUESTED', $group->getJoinedDate($user->id, SOCIAL_TYPE_USER, true)); ?>
								</li>
								<?php } ?>
							</ol>
						</div>
					</div>

					<div class="es-list-item__action">
						<div role="toolbar" class="btn-toolbar t-lg-mt--sm">
							<?php echo $this->html('user.conversation', $user); ?>

							<?php echo $this->html('user.clusterActions', $user, $group); ?>
						</div>
					</div>

				</div>

			</div>

		</div>
	</div>
	<?php } ?>
<?php } ?>
</div>
<?php echo $this->html('html.emptyBlock', $emptyText, 'fa-users'); ?>

<?php if ($pagination) { ?>
<div class="es-pagination-footer">
	<?php echo $pagination->getListFooter('site');?>
</div>
<?php } ?>
