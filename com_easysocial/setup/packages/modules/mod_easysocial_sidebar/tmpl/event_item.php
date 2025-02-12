<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
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
		<?php echo $this->lib->render('module', 'es-events-item-sidebar-top' , 'site/dashboard/sidebar.module.wrapper'); ?>
		<?php echo $this->lib->render('widgets', SOCIAL_TYPE_EVENT, 'events', 'sidebarTop', array('uid' => $event->id, 'event' => $event)); ?>

		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_ES_INFORMATION'); ?>

			<div class="es-side-widget__bd">

				<?php if (!$this->lib->isMobile() && $this->lib->config->get('events.layout.description')) { ?>
					<?php echo $this->lib->html('string.truncate', $event->getDescription(), 120, '', false, false, false, true);?>
					<a href="<?php echo $helper->getAboutPermalink();?>"><?php echo JText::_('COM_EASYSOCIAL_READMORE');?></a>
				<?php } ?>

				<ul class="o-nav o-nav--stacked t-lg-mt--sm">
					<li class="o-nav__item t-text--muted t-lg-mb--sm">
						<a href="<?php echo $event->getCreator()->getPermalink();?>" class="o-nav__link t-text--muted">
							<i class="fa fa-user"></i>&nbsp;
							<?php echo $event->getCreator()->getName();?>
						</a>
					</li>

					<li class="o-nav__item t-text--muted t-lg-mb--sm">
						<a href="<?php echo $event->getAppPermalink('guests');?>" class="o-nav__link t-text--muted">
							<i class="fa fa-users"></i>&nbsp;
							<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_EVENTS_TOTAL_GUESTS', $event->getTotalGoing()), $event->getTotalGoing()); ?>
						</a>
					</li>

					<?php if ($this->lib->config->get('events.layout.address') && $event->address) { ?>
					<li class="o-nav__item t-text--muted t-lg-mb--sm">
						<i class="fa fa-map-marker-alt"></i>&nbsp; <a href="<?php echo $event->getAddressLink(); ?>" target="_blank"><?php echo $event->address;?></a>
					</li>
					<?php } ?>

					<?php if ($this->lib->config->get('events.layout.seatsleft') && $event->seatsLeft() > 0) { ?>
					<li class="o-nav__item t-text--muted t-lg-mb--sm">
						<i class="fa fa-ticket"></i>&nbsp; <?php echo JText::sprintf('COM_EASYSOCIAL_EVENTS_SEATS_REMAINING', '<b>' . $event->seatsLeft() . '</b>', '<b>' . $event->getTotalSeats() . '</b>'); ?>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>

		<?php echo $this->lib->render('widgets', SOCIAL_TYPE_EVENT, 'events', 'sidebarMiddle', array('uid' => $event->id, 'event' => $event)); ?>

		<?php echo $this->lib->render('widgets', SOCIAL_TYPE_EVENT, 'events', 'sidebarBottom', array('uid' => $event->id, 'event' => $event)); ?>

		<?php echo $this->lib->render('module', 'es-events-item-sidebar-bottom' , 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
</div>
