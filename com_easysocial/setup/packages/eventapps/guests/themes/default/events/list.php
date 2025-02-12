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
<?php if ($filter == 'pending' && $pending) { ?>
	<div class="es-list-item-actions">
		<div class="o-grid-sm o-grid-sm--center">
			<div class="o-grid-sm__cell">
				<div class="o-checkbox t-xs-ml--lg">
					<input type="checkbox" id="es-media-select-all-checkbox" data-event-item-checkall>
					<label for="es-media-select-all-checkbox">
							<?php echo JText::_('COM_ES_SELECT_ALL'); ?>
					</label>
				</div>
			</div>
			<div class="o-grid-sm__cell o-grid-sm__cell--right">
				<div class="t-hidden" data-event-actions-wrapper>
					<div class="o-select-group o-select-group--inline">
						<select class="o-form-control" data-event-actions-task>
							<option><?php echo JText::_('COM_ES_BULK_ACTIONS'); ?></option>
							<?php ?>
							<option value="site/controllers/events/approveGuest"
									data-confirmation="site/views/events/confirmApproveGuest"
									>
									<?php echo JText::_('COM_ES_BULK_ACTIONS_APPROVE'); ?>
							</option>
							<?php  ?>
							<?php ?>
							<option value="site/controllers/events/rejectGuest"
									data-confirmation="site/views/events/confirmRejectGuest"
									>
									<?php echo JText::_('COM_ES_BULK_ACTIONS_REJECT'); ?>
							</option>
							<?php  ?>
						</select>
						<label for="" class="o-select-group__drop"></label>
					</div>

					<a href="javascript:void(0);" class="btn btn-es-primary-o" data-event-actions-apply><?php echo JText::_('COM_ES_APPLY'); ?></a>
				</div>
			</div>
		</div>
	</div>
<?php } ?>
<div class="es-list" data-result>
	<?php if ($guests) { ?>
		<?php foreach ($guests as $guest) { ?>
		<div class="es-list__item">
			<div class="es-list-item es-island" data-item data-id="<?php echo $guest->id;?>" data-user-id="<?php echo $guest->user->id; ?>" data-return="<?php echo $returnUrl;?>">

				<?php if ($filter == 'pending') { ?>
					<div class="es-list-item__checkbox">
						<div class="o-checkbox">
							<input type="checkbox" id="es-event-guest-select-checkbox-<?php echo $guest->id;?>" value="<?php echo $guest->id; ?>" data-event-item-checkbox>
							<label for="es-event-guest-select-checkbox-<?php echo $guest->id;?>">&nbsp;</label>
						</div>
					</div>
				<?php } ?>

				<div class="es-list-item__media">
					<?php echo $this->html('avatar.user', $guest->user, 'md', true); ?>
				</div>

				<div class="es-list-item__context">
					<div class="es-list-item__hd">
						<div class="es-list-item__content">

							<div class="es-list-item__title">
								<?php echo $this->html('html.user', $guest->user); ?>
							</div>

							<div class="es-list-item__meta">
								<?php if ($event->isOwner($guest->uid)) { ?>
								<span class="o-label o-label--primary-o label-owner"><?php echo JText::_('APP_EVENT_GUESTS_OWNER'); ?></span>
								<?php } ?>

								<?php if ($event->isAdmin($guest->uid) && !$event->isOwner($guest->uid)) { ?>
								<span class="o-label o-label--danger-o label-admin"><?php echo JText::_('APP_EVENT_GUESTS_ADMIN'); ?></span>
								<?php } ?>

								<?php if (!$event->isAdmin($guest->uid) && $guest->user->isSiteAdmin()) { ?>
								<span class="o-label o-label--info-o label-moderator"><?php echo JText::_('APP_EVENT_GUESTS_MODERATOR'); ?></span>
								<?php } ?>

								<?php if ($guest->isGoing()) { ?>
								<span class="o-label o-label--success-o label-going"><?php echo JText::_('APP_EVENT_GUESTS_GOING'); ?></span>
								<?php } ?>

								<?php if ($guest->isNotGoing()) { ?>
								<span class="o-label o-label--warning-o label-not-going"><?php echo JText::_('APP_EVENT_GUESTS_NOT_GOING'); ?></span>
								<?php } ?>

								<?php if ($guest->isMaybe()) { ?>
								<span class="o-label o-label--info-o label-maybe"><?php echo JText::_('APP_EVENT_GUESTS_MAYBE'); ?></span>
								<?php } ?>

								<?php if ($guest->isPending()) { ?>
								<span class="o-label o-label--warning-o label-pending"><?php echo JText::_('APP_EVENT_GUESTS_PENDING'); ?></span>
								<?php } ?>

								<?php if ($guest->isInvited()) { ?>
								<span class="o-label o-label--warning-o label-pending-invitation"><?php echo JText::_('APP_EVENT_GUESTS_INVITED'); ?></span>

								<span data-breadcrumb="&#183;">
									<?php echo JText::sprintf('APP_EVENT_GUESTS_INVITED_BY', $this->html('html.user', $guest->getInvitor($guest->user->id)->id, true)); ?>
								</span>
								<?php } ?>
							</div>
						</div>
						<div class="es-list-item__state">
							<div class="es-label-state es-label-state--featured" data-original-title="Featured" data-es-provide="tooltip">
								<i class="es-label-state__icon"></i>
							</div>
						</div>

						<div class="es-list-item__action">
							<div role="toolbar" class="btn-toolbar pull-right">

								<?php echo $this->html('user.conversation', $guest->user); ?>

								<?php if (($event->isAdmin() || $this->my->isSiteAdmin()) && !$event->isOwner($guest->user->id)) { ?>
								<div role="group" class="o-btn-group">
									<button data-es-toggle="dropdown" class="btn btn-es-default-o btn-sm dropdown-toggle_" type="button">
										 <i class="fa fa-ellipsis-h"></i>
									</button>

									<ul class="dropdown-menu dropdown-menu-right">
										<?php if (($myGuest->isOwner() || $this->my->isSiteAdmin()) && $guest->isStrictlyAdmin() && !$guest->isInvited()) { ?>
										<li>
											<a href="javascript:void(0);" data-guest-demote><?php echo JText::_('APP_EVENT_GUESTS_REVOKE_ADMIN'); ?></a>
										</li>
										<li class="divider"></li>
										<?php } ?>

										<?php if (($myGuest->isOwner() || $this->my->isSiteAdmin()) && !$guest->isStrictlyAdmin() && !$guest->isInvited()) { ?>
										<li>
											<a href="javascript:void(0);" data-guest-promote><?php echo JText::_('APP_EVENT_GUESTS_MAKE_ADMIN'); ?></a>
										</li>
										<li class="divider"></li>
										<?php } ?>

										<?php if ($guest->isPending()) { ?>
										<li>
											<a href="javascript:void(0);" data-guest-approve><?php echo JText::_('APP_EVENT_GUESTS_APPROVE_REQUEST'); ?></a>
										</li>
										<li>
											<a href="javascript:void(0);" data-guest-reject><?php echo JText::_('APP_EVENT_GUESTS_REJECT_REQUEST'); ?></a>
										</li>
										<li class="divider"></li>
										<?php } ?>

										<?php if ($myGuest->isOwner() || $this->my->isSiteAdmin() || ($myGuest->isAdmin() && !$guest->isAdmin())) { ?>
										<li>
											<a href="javascript:void(0);" data-guest-remove><?php echo JText::_('APP_EVENT_GUESTS_REMOVE_FROM_EVENT'); ?></a>
										</li>
										<?php } ?>
									</ul>
								 </div>
								 <?php } ?>
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
