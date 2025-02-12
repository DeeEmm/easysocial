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
<div class="popbox-dropdown" data-popbox-notifications-friends>
	<div class="popbox-dropdown__hd">
		<div class="o-flag o-flag--rev">
			<div class="o-flag__body">
				<div class="popbox-dropdown__title t-lg-pull-left"><?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_FRIEND_REQUESTS');?></div>


			</div>
		</div>
	</div>

	<div class="popbox-dropdown__bd">
		<div class="popbox-dropdown-nav">
			<?php if ($requests) { ?>
				<?php foreach ($requests as $request) { ?>
				<div class="popbox-dropdown-nav__item xis-unread" data-item data-id="<?php echo $request->getRequester()->id;?>">
					<span class="popbox-dropdown-nav__link">
						<div class="o-flag">
							<div class="o-flag__image o-flag--top">
								<?php if (!$this->isMobile()) { ?>
									<?php echo $this->html('avatar.user', $request->getRequester(), 'default', false); ?>
								<?php } ?>
								<?php if ($this->isMobile()) { ?>
									<?php echo $this->html('avatar.user', $request->getRequester(), 'lg', false); ?>
								<?php } ?>
							</div>
							<div class="o-flag__body">
								<div class="popbox-dropdown-nav__post">
									<div class="<?php echo $this->isMobile() ? 't-fs--lg' : '';?>">
										<?php echo $this->html('html.user', $request->getRequester(), false); ?>
									</div>
									<span class="<?php echo $this->isMobile() ? '' : 't-fs--sm';?>">
										<?php if ($request->getRequester()->getTotalMutualFriends($this->my->id)) { ?>
											<?php echo JText::sprintf('COM_EASYSOCIAL_TOOLBAR_FRIENDS_MUTUAL_FRIENDS_TOTAL', $request->getRequester()->getTotalMutualFriends($this->my->id)); ?>
										<?php } else { ?>
											<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_FRIENDS_NO_MUTUAL_FRIENDS'); ?>
										<?php } ?>
									</span>
								</div>
								<div class="t-lg-mt--md" data-actions>
									<div>
										<a href="javascript:void(0);" class="btn btn-es-primary btn-sm t-lg-mr--sm" data-action="accept">
											<?php echo JText::_('COM_EASYSOCIAL_ACCEPT_BUTTON');?>
										</a>
										<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-action="reject">
											<?php echo JText::_('COM_EASYSOCIAL_REJECT_BUTTON');?>
										</a>
									</div>
								</div>

							</div>
						</div>
					</span>
				</div>
				<?php } ?>
			<?php } else { ?>
			<div class="is-empty t-text--muted">
				<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_TOOLBAR_FRIENDS_NO_FRIENDS_YET', 'fa-user-friends', false, false); ?>
			</div>
			<?php } ?>
		</div>
	</div>
	<div class="popbox-dropdown__ft">
		<a href="<?php echo ESR::friends(array('filter' => 'pending'));?>" class="popbox-dropdown__note"><?php echo JText::_('COM_ES_VIEW_ALL');?></a>
	</div>
</div>
