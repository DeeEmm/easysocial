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
<div class="es-list__item">
	<div class="es-list-item es-island" data-item data-id="<?php echo $user->id;?>">

		<div class="es-list-item__media">
			<?php echo $this->html('avatar.user', $user); ?>
		</div>

		<div class="es-list-item__context">
			<div class="es-list-item__hd">
				<div class="es-list-item__content">

					<div class="es-list-item__title">
						<?php echo $this->html('html.user', $user); ?>
					</div>

					<?php if (!$this->isMobile()) { ?>
					<div class="es-list-item__meta">
						<ol class="g-list-inline g-list-inline--delimited es-user-item-metax">
							<?php if ($displayType) { ?>
							<li data-breadcrumb="&#183;">
								<i class="fa fa-user"></i>&nbsp; <?php echo JText::_('COM_ES_USERS');?>
							</li>
							<?php } ?>

							<?php if ($this->config->get('friends.enabled') && $this->my->canView($user, 'friends.view')) { ?>
							<li data-breadcrumb="&#183;">
								<a href="<?php echo ESR::friends(array('userid' => $user->getAlias()));?>" class="t-text--muted">
									<?php if ($user->getTotalFriends()) { ?>
										<?php echo $user->getTotalFriends();?> <?php echo JText::_(ES::string()->computeNoun('COM_EASYSOCIAL_FRIENDS', $user->getTotalFriends())); ?>
									<?php } else { ?>
										<?php echo JText::_('COM_EASYSOCIAL_NO_FRIENDS_YET'); ?>
									<?php } ?>
								</a>
							</li>
							<?php } ?>

							<?php if ($this->config->get('followers.enabled') && $this->my->canView($user, 'followers.view')) { ?>
							<li data-breadcrumb="&#183;">
								<a href="<?php echo ESR::followers(array('userid' => $user->getAlias()));?>" class="t-text--muted">
									<?php if ($user->getTotalFollowers()) { ?>
										<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_FOLLOWERS', $user->getTotalFollowers()), $user->getTotalFollowers()); ?>
									<?php } else { ?>
										<?php echo JText::_('COM_EASYSOCIAL_NO_FOLLOWERS_YET'); ?>
									<?php } ?>
								</a>
							</li>
							<?php } ?>

							<?php if ($this->config->get('badges.enabled')) { ?>
							<li data-breadcrumb="&#183;">
								<a href="<?php echo ESR::badges(array('userid' => $user->getAlias(), 'layout' => 'achievements'));?>" class="t-text--muted">
									<?php if( $user->getTotalbadges() ){ ?>
										<?php echo $user->getTotalbadges();?> <?php echo JText::_(ES::string()->computeNoun('COM_EASYSOCIAL_BADGES', $user->getTotalbadges())); ?>
									<?php } else { ?>
										<?php echo JText::_('COM_EASYSOCIAL_NO_BADGES_YET'); ?>
									<?php } ?>
								</a>
							</li>
							<?php } ?>
						</ol>
					</div>
					<?php } ?>
				</div>


				<div class="es-list-item__action">
					<?php if (!$this->isMobile()) { ?>
					<div role="toolbar" class="btn-toolbar t-lg-mt--sm">
						<?php if ($showRemoveFromList) { ?>
						<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-remove-from-list><?php echo JText::_('COM_EASYSOCIAL_FRIENDS_REMOVE_FROM_LIST');?></a>
						<?php } ?>

						<?php echo $this->html('user.subscribe', $user); ?>

						<?php echo $this->html('user.friends', $user); ?>

						<?php if ($this->my->getPrivacy()->validate('profiles.post.message', $user->id, SOCIAL_TYPE_USER)) { ?>
							<?php echo $this->html('user.conversation', $user, 'sm', ES::conversekit()->hasActivateLinkableCK()); ?>
						<?php } ?>

						<?php echo $this->html('user.actions', $user); ?>
					</div>
					<?php } ?>

				</div>
			</div>

			<?php if ($this->isMobile()) { ?>
			<div class="es-list-item__bd">

				<div role="toolbar" class="btn-toolbar">
					<?php if ($showRemoveFromList) { ?>
					<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-remove-from-list><?php echo JText::_('COM_EASYSOCIAL_FRIENDS_REMOVE_FROM_LIST');?></a>
					<?php } ?>

					<?php echo $this->html('user.friends', $user); ?>

					<?php echo $this->html('user.subscribe', $user); ?>

					<?php if ($this->my->getPrivacy()->validate('profiles.post.message', $user->id, SOCIAL_TYPE_USER)) { ?>
						<?php echo $this->html('user.conversation', $user, 'sm', ES::conversekit()->hasActivateLinkableCK()); ?>
					<?php } ?>

					<?php echo $this->html('user.actions', $user); ?>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
</div>
