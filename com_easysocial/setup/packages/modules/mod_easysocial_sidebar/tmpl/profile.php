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
<div id="es" class="mod-es mod-es-sidebar <?php echo $this->lib->getSuffix();?>">
	<div class="es-sidebar" data-sidebar>

		<?php echo $this->lib->render('widgets', 'user', 'profile', 'sidebarBeforeIntro', array($user), 'site/widgets/sidebar.wrapper'); ?>

		<?php if ($this->lib->config->get('users.layout.joindate', true) || $this->lib->config->get('users.layout.badges', true) || $this->lib->config->get('users.layout.points', true) || $this->lib->config->get('users.layout.gender', true)|| $this->lib->config->get('users.layout.age', true)|| $this->lib->config->get('users.layout.address', true)) { ?>
		<div class="es-side-widget" data-type="info">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_USER_INTRO'); ?>

			<div class="es-side-widget__bd">

				<ul class="o-nav o-nav--stacked">
					<?php if ($this->lib->config->get('users.layout.joindate', true)) { ?>
					<li class="o-nav__item t-text--muted  t-lg-mb--sm">
						<i class="es-side-widget__icon fa fa-home t-lg-mr--md"></i>
						<?php echo JText::sprintf('COM_ES_JOINED_ON', $user->getRegistrationDate()->toLapsed()); ?>
					</li>
					<?php } ?>

					<?php if ($this->lib->config->get('badges.enabled') && $user->badgesViewable($this->lib->my->id) && $this->lib->config->get('users.layout.badges', true)) { ?>
					<li class="o-nav__item t-lg-mb--sm">
						<a class="o-nav__link t-text--muted" href="<?php echo ESR::badges(array('layout' => 'achievements', 'userid' => $user->getAlias()));?>">
							<i class="es-side-widget__icon fa fa-trophy t-lg-mr--md"></i>
							<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_ACHIEVEMENTS', $user->getTotalBadges()), $user->getTotalBadges()); ?>
						</a>
					</li>
					<?php } ?>

					<?php if ($this->lib->config->get('points.enabled') && $this->lib->config->get('users.layout.points', true)) { ?>
					<li class="o-nav__item t-lg-mb--sm">
						<?php if ($this->lib->my->canViewPointsHistory($user)) { ?>
						<a class="o-nav__link t-text--muted" href="<?php echo ESR::points(array('layout' => 'history', 'userid' => $user->getAlias()));?>">
						<?php } else { ?>
						<a class="o-nav__link t-text--muted" href="javascript:void(0);">
						<?php } ?>
							<i class="es-side-widget__icon fa fa-certificate t-lg-mr--md"></i>
							<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_POINTS', $user->getPoints()), $user->getPoints()); ?>
						</a>
					</li>
					<?php } ?>

					<?php if ($this->lib->config->get('users.layout.gender', true)) { ?>
					<li class="o-nav__item t-lg-mb--sm">
						<?php echo $this->lib->render('fields', 'user', 'profile', 'profileIntro', array('GENDER', $user)); ?>
					</li>
					<?php } ?>

					<?php if ($this->lib->config->get('users.layout.age', true)) { ?>
					<li class="o-nav__item t-lg-mb--sm">
						<?php echo $this->lib->render('fields', 'user', 'profile', 'profileIntro', array('BIRTHDAY', $user)); ?>
					</li>
					<?php } ?>

					<?php if ($this->lib->config->get('users.layout.address', true)) { ?>
					<li class="o-nav__item">
						<?php echo $this->lib->render('fields', 'user', 'profile', 'profileIntro', array('ADDRESS', $user)); ?>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php } ?>

		<?php echo $this->lib->render('module', 'es-profile-sidebar-top' , 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php echo $this->lib->render('widgets', 'user', 'profile', 'sidebarTop', array($user), 'site/widgets/sidebar.wrapper'); ?>

		<?php echo $this->lib->render('widgets', 'user', 'profile', 'sidebarBottom', array($user), 'site/widgets/sidebar.wrapper'); ?>

		<?php echo $this->lib->render('module', 'es-profile-sidebar-bottom' , 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
</div>
