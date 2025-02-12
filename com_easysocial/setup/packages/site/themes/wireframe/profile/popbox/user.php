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
<div class="popbox-content__bd">
	<div class="o-media o-media--rev t-lg-mb--lg">
		<div class="o-media__image">
			<?php echo $this->html('avatar.user', $user, 'md', false); ?>
		</div>
		<div class="o-media__body">
			<div class="o-title t-text--truncate">
				<?php echo $this->html('html.user', $user, false); ?>

				<?php if ($this->config->get('users.layout.lastonline')) { ?>
				<div class="o-meta">
					<?php if($user->getLastVisitDate() == '0000-00-00 00:00:00') { ?>
						<?php echo JText::_('COM_EASYSOCIAL_USER_NEVER_LOGGED_IN');?>
					<?php } elseif (!$user->isOnline()) { ?>
						<?php echo JText::sprintf('COM_EASYSOCIAL_LAST_LOGGED_IN', $user->getLastVisitDate('lapsed'));?>
					<?php } ?>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>

	<?php if ($user->hasCommunityAccess()) { ?>
	<div class="popbox-label-group t-lg-mb--md">

		<?php if ($this->config->get('friends.enabled')) { ?>
		<div class="popbox-label t-text--truncate">
			<a href="<?php echo ESR::friends(array('userid' => $user->getAlias()));?>">
				<?php echo $user->getTotalFriends();?> <span class="popbox-label__meta"><?php echo JText::_('COM_EASYSOCIAL_FRIENDS');?></span>
			</a>
		</div>
		<?php } ?>

		<?php if ($this->config->get('photos.enabled')) { ?>
		<div class="popbox-label t-text--truncate">
			<a href="<?php echo ESR::albums(array('uid' => $user->getAlias(), 'type' => SOCIAL_TYPE_USER));?>">
				<?php echo $user->getTotalAlbums();?> <span class="popbox-label__meta"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_ALBUMS');?></span>
			</a>
		</div>
		<?php } ?>

		<?php if ($this->config->get('followers.enabled')) { ?>
		<div class="popbox-label t-text--truncate">
			<a href="<?php echo ESR::followers(array('userid' => $user->getAlias()));?>">
				<?php echo $user->getTotalFollowers();?> <span class="popbox-label__meta"><?php echo JText::_('COM_EASYSOCIAL_FOLLOWERS');?></span>
			</a>
		</div>
		<?php } ?>
	</div>
	<?php } ?>
</div>

<?php if ($user->hasCommunityAccess() && $badges) { ?>
<div class="popbox-content__bd">
	<?php foreach ($badges as $badge) { ?>
		<?php echo $this->html('avatar.mini', $badge->getTitle(), $badge->getPermalink(), $badge->getAvatar(), 'sm', '', array('data-es-provide="tooltip"', 'data-original-title="' . $badge->getTitle() . '"')); ?>
	<?php } ?>
</div>
<?php } ?>

<?php if ($user->hasCommunityAccess() && !$user->isViewer() && !$user->isBlockedBy($this->my->id)) { ?>
<div class="popbox-content__ft">
	<?php echo $this->html('user.friends', $user); ?>
	<?php echo $this->html('user.subscribe', $user); ?>

	<?php if ($this->my->getPrivacy()->validate('profiles.post.message', $user->id, SOCIAL_TYPE_USER)) { ?>
		<?php echo $this->html('user.conversation', $user, 'sm', ES::conversekit()->hasActivateLinkableCK()); ?>
	<?php } ?>
</div>
<?php } ?>
