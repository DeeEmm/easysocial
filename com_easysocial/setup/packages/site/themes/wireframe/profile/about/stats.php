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
<div class="es-side-widget" data-type="info">
	<?php echo $this->html('widget.title', 'COM_ES_USER_STATS'); ?>

	<div class="es-side-widget__bd">

		<ul class="o-nav o-nav--stacked">
			<?php if ($this->config->get('badges.enabled') && $user->badgesViewable($this->my->id)) { ?>
			<li class="o-nav__item t-lg-mb--sm">
				<a class="o-nav__link t-text--muted" href="<?php echo ESR::badges(array('layout' => 'achievements', 'userid' => $user->getAlias()));?>">
					<i class="es-side-widget__icon fa fa-trophy t-lg-mr--md"></i>
					<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_ACHIEVEMENTS', $user->getTotalBadges()), '<b>' . $user->getTotalBadges() . '</b>'); ?>
				</a>
			</li>
			<?php } ?>

			<?php if( $this->config->get('points.enabled') && $this->config->get('users.layout.points', true)) { ?>
			<li class="o-nav__item t-lg-mb--sm">
				<?php if ($this->my->canViewPointsHistory($user)) { ?>
				<a class="o-nav__link t-text--muted" href="<?php echo ESR::points(array('layout' => 'history', 'userid' => $user->getAlias()));?>">
				<?php } else { ?>
				<a class="o-nav__link t-text--muted" href="javascript:void(0);">
				<?php } ?>
					<i class="es-side-widget__icon fa fa-certificate t-lg-mr--md"></i>
					<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_POINTS', $user->getPoints()), '<b>' . $user->getPoints() . '</b>'); ?>
				</a>
			</li>
			<?php } ?>

			<?php if ($this->config->get('friends.enabled')) { ?>
			<li class="o-nav__item t-lg-mb--sm">
				<a href="<?php echo ESR::friends(array('userid' => $user->getAlias()));?>" class="o-nav__link t-text--muted">
					<i class="es-side-widget__icon fa fa-user-friends t-lg-mr--md"></i>
					<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GENERIC_FRIENDS', $user->getTotalFriends()), '<b>' . $user->getTotalFriends() . '</b>'); ?>
				</a>
			</li>
			<?php } ?>

			<?php if ($user->canCreateAlbums()) { ?>
			<li class="o-nav__item t-lg-mb--sm">
				<a href="<?php echo ESR::albums(array('uid' => $user->getAlias(), 'type' => SOCIAL_TYPE_USER));?>" class="o-nav__link t-text--muted">
					<i class="es-side-widget__icon far fa-images t-lg-mr--md"></i>
					<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_USER_ALBUMS' , $user->getTotalAlbums()), '<b>' . $user->getTotalAlbums() . '</b>'); ?>
				</a>
			</li>
			<?php } ?>

			<?php if ($user->canCreateVideos()) { ?>
			<li class="o-nav__item t-lg-mb--sm">
				<a href="<?php echo ESR::videos(array('uid' => $user->getAlias(), 'type' => SOCIAL_TYPE_USER));?>" class="o-nav__link t-text--muted">
					<i class="es-side-widget__icon fa fa-film t-lg-mr--md"></i>
					<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GROUPS_VIDEOS' , $user->getTotalVideos()), '<b>' . $user->getTotalVideos() . '</b>'); ?>
				</a>
			</li>
			<?php } ?>

			<?php if ($user->canCreateAudios()) { ?>
			<li class="o-nav__item t-lg-mb--sm">
				<a href="<?php echo ESR::audios(array('uid' => $user->getAlias(), 'type' => SOCIAL_TYPE_USER));?>" class="o-nav__link t-text--muted">
					<i class="es-side-widget__icon fa fa-music t-lg-mr--md"></i>
					<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_CLUSTERS_AUDIO' , $user->getTotalAudios()), '<b>' . $user->getTotalAudios() . '</b>'); ?>
				</a>
			</li>
			<?php } ?>

			<?php if ($this->config->get('groups.enabled')) { ?>
			<li class="o-nav__item t-lg-mb--sm">
				<a href="<?php echo ESR::groups(array('userid' => $user->getAlias()));?>" class="o-nav__link t-text--muted">
					<i class="es-side-widget__icon fa fa-users t-lg-mr--md"></i>
					<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_PROFILE_GROUPS', $user->getTotalGroups()), '<b>' . $user->getTotalGroups() . '</b>'); ?>
				</a>
			</li>
			<?php } ?>

			<?php if ($this->config->get('events.enabled')) { ?>
			<li class="o-nav__item t-lg-mb--sm">
				<a href="<?php echo ESR::events(array('userid' => $user->getAlias()));?>" class="o-nav__link t-text--muted">
					<i class="es-side-widget__icon far fa-calendar-alt t-lg-mr--md"></i>
					<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_PROFILE_EVENTS', $user->getTotalCreatedJoinedEvents()), '<b>' . $user->getTotalCreatedJoinedEvents() . '</b>'); ?>
				</a>
			</li>
			<?php } ?>

			<?php if ($this->config->get('pages.enabled')) { ?>
			<li class="o-nav__item t-lg-mb--sm">
				<a href="<?php echo ESR::pages(array('userid' => $user->getAlias()));?>" class="o-nav__link t-text--muted">
					<i class="es-side-widget__icon fa fa-cube t-lg-mr--md"></i>
					<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_PROFILE_PAGES', $user->getTotalPages()), '<b>' . $user->getTotalPages() . '</b>'); ?>
				</a>
			</li>
			<?php } ?>

			<?php if ($this->config->get('followers.enabled')) { ?>
			<li class="o-nav__item">
				<a href="<?php echo ESR::followers(array('userid' => $user->getAlias()));?>" class="o-nav__link t-text--muted">
					<i class="es-side-widget__icon fa fa-user-secret t-lg-mr--md"></i>
					<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_FOLLOWERS', $user->getTotalFollowers()), '<b>' . $user->getTotalFollowers() . '</b>'); ?>
				</a>
			</li>
			<?php } ?>

			<?php if ($this->config->get('polls.enabled')) { ?>
			<li class="o-nav__item">
				<a href="<?php echo ESR::polls(array('userid' => $user->getAlias()));?>" class="o-nav__link t-text--muted">
					<i class="es-side-widget__icon fa fa-chart-pie t-lg-mr--md"></i>
					<b><?php echo $user->getTotalPolls();?></b> <?php echo JText::_('COM_EASYSOCIAL_POLLS');?>
				</a>
			</li>
			<?php } ?>
		</ul>
	</div>
</div>
