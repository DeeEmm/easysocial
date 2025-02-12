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
<div class="es-cards__item">
	<div class="es-card" data-item data-id="<?php echo $user->id;?>">
		<div class="es-card__hd">
			<div class="es-card__action-group">
				<div class="es-card__admin-action">
					<?php echo $this->html('user.actions', $user); ?>
				</div>
			</div>

			<?php echo $this->html('card.cover', $user); ?>
		</div>

		<div class="es-card__bd es-card--border has-avatar t-text--center">
			<?php echo $this->html('card.avatar', $user, 'center'); ?>

			<?php echo $this->html('card.title', $user); ?>

			<div class="es-card__meta t-lg-mb--sm">
				<ul class="g-list--horizontal has-dividers--right t-justify-content--c">

					<?php if ($this->config->get('friends.enabled') && $this->my->canView($user, 'friends.view')) { ?>
					<li class="g-list__item">
						<a href="<?php echo ESR::friends(array('userid' => $user->getAlias()));?>">
							<?php if ($user->getTotalFriends()) { ?>
								<?php echo $user->getTotalFriends();?> <?php echo JText::_(ES::string()->computeNoun('COM_EASYSOCIAL_FRIENDS', $user->getTotalFriends())); ?>
							<?php } else { ?>
								<?php echo JText::_('COM_EASYSOCIAL_NO_FRIENDS_YET'); ?>
							<?php } ?>
						</a>
					</li>
					<?php } ?>

					<?php if ($this->config->get('followers.enabled') && $this->my->canView($user, 'followers.view')) { ?>
					<li class="g-list__item">
						<a href="<?php echo ESR::followers(array('userid' => $user->getAlias()));?>">
							<?php if ($user->getTotalFollowers()) { ?>
								<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_FOLLOWERS', $user->getTotalFollowers()), $user->getTotalFollowers()); ?>
							<?php } else { ?>
								<?php echo JText::_('COM_EASYSOCIAL_NO_FOLLOWERS_YET'); ?>
							<?php } ?>
						</a>
					</li>
					<?php } ?>
				</ul>
			</div>

			<?php if ($badges) { ?>
			<div class="t-lg-pt--md">
				<?php foreach ($badges as $badge) { ?>
					<?php echo $this->html('avatar.mini', $badge->getTitle(), $badge->getPermalink(), $badge->getAvatar(), 'sm', '', array('data-es-provide="tooltip"', 'data-original-title="' . $badge->getTitle() . '"')); ?>
				<?php } ?>
			</div>
			<?php } ?>
		</div>

		<div class="es-card__ft es-card--border">
			<div class="es-card__meta">
				<div role="toolbar" class="btn-toolbar btn-toolbar--align-center  t-lg-mt--sm">
					<?php echo $this->html('user.friends', $user); ?>

					<?php echo $this->html('user.subscribe', $user); ?>

					<?php if ($this->my->getPrivacy()->validate('profiles.post.message', $user->id, SOCIAL_TYPE_USER) && $this->my->canStartConversation($user->id)) { ?>
						<?php echo $this->html('user.conversation', $user); ?>
					<?php } ?>
				</div>
			</div>
	   </div>
	</div>
</div>