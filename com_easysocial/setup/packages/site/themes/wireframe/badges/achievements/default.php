<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php echo $this->html('html.miniheader', $user); ?>

<div class="es-container" data-achievements data-es-container>

	<div class="es-content">
		<?php echo $this->render('module', 'es-achievements-before-contents'); ?>

		<div class="es-stage es-island">

			<div class="es-stage__curtain es-bleed--top">

				<h3 class="es-stage__title">
					<?php if ($user->id == $this->my->id) { ?>
						<?php echo JText::sprintf('COM_EASYSOCIAL_BADGES_USERNAME', ucfirst($user->getName())); ?>
					<?php } else { ?>
						<?php echo JText::sprintf('COM_EASYSOCIAL_BADGES_USERS_BADGES', ucfirst($user->getName())); ?>
					<?php } ?>
				</h3>
				<div class="es-stage__desc">
					 <?php if ($user->isViewer()) { ?>
						<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_BADGES_TOTAL_ACHIEVEMENTS', $totalBadges), $totalBadges); ?>
						<a href="<?php echo ESR::badges();?>"><?php echo JText::_('COM_EASYSOCIAL_UNLOCK_MORE_BADGES');?></a>
					 <?php } else { ?>
						<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_VISITOR_BADGES_TOTAL_ACHIEVEMENTS', $totalBadges), $user->getName(), $totalBadges); ?>
					 <?php } ?>
				</div>
				<div class="es-stage__actor">
					<?php echo $this->html('avatar.user', $user, 'xl', false, false, '', false); ?>
				</div>

			</div>

			<div class="es-stage__audience">
				<div class="es-stage__audience-result">
					<div class="es-achievements <?php echo !$badges ? 'is-empty' : '';?>" data-achievements-content>
						<?php echo $this->render('module', 'es-achievements-between-achievement'); ?>

						<?php if ($badges) { ?>
							<div class="es-cards es-cards--4">
								<?php foreach ($badges as $badge) { ?>
								<div class="es-cards__item es-achieve-badge">
									<div class="es-achieve-badge__img-wrap">
										<a href="<?php echo $badge->getPermalink();?>">
											<img src="<?php echo $badge->getAvatar();?>" alt="<?php echo $this->html('string.escape', $badge->_('title'));?>" title="<?php echo $this->html('string.escape', $badge->_('title'));?>" />
										</a>
									</div>
									<div class="es-achieve-badge__title">
										<a href="<?php echo $badge->getPermalink();?>"><?php echo $badge->_('title'); ?></a>
									</div>
									<div class="es-achieve-badge__desc">
										<?php echo $this->html('string.escape', $badge->custom_message ? $badge->custom_message : $badge->_('description'));?>
									</div>

									<div class="es-achieve-badge__date">
										<?php echo $badge->getAchievedDate()->format(JText::_('DATE_FORMAT_LC1')); ?>
									</div>
								</div>
								<?php } ?>
							</div>
						<?php } ?>

						<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_BADGES_NO_ACHIEVEMENTS_YET', 'fa-trophy'); ?>
					</div>
				</div>

			</div>
		</div>
		<?php echo $this->render('module', 'es-achievements-after-contents'); ?>
	</div>
</div>
