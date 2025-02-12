<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
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
	<div class="es-list-item es-island <?php echo $event->isFeatured() ? ' is-featured' : '';?> <?php echo $event->isPassed() ? ' is-passed' : '';?>" data-item data-id="<?php echo $event->id;?>">
		<div class="es-list-item__media">
			<?php echo $this->html('avatar.cluster', $event); ?>
		</div>

		<div class="es-list-item__context">
			<div class="es-list-item__hd">
				<div class="es-list-item__content">
					<div class="es-list-item__title">
						<?php echo $this->html('html.cluster', $event); ?>
					</div>

					<div class="es-list-item__meta">
						<ol class="g-list-inline g-list-inline--delimited t-text--muted">
							<?php if ($displayType) { ?>
							<li>
								<i class="far fa-calendar-alt"></i>&nbsp; <?php echo JText::_('COM_ES_EVENTS');?>
							</li>
							<?php } ?>

							<?php if (!$this->isMobile()) { ?>
							<li data-breadcrumb="&#183;">
								<?php echo $this->html('event.type', $event, 'bottom', false, false); ?>
							</li>

							<li data-breadcrumb="&#183;">
								<a href="<?php echo $event->getCategory()->getPermalink();?>"><?php echo $event->getCategory()->getTitle();?></a>
							</li>

							<li data-breadcrumb="&#183;">
								<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_EVENTS_GUESTS', $event->getTotalGoing()), $event->getTotalGoing() ); ?>
							</li>
							<?php } ?>

							<li data-breadcrumb="&#183;">
								<?php echo $event->getStartEndDisplay(array('timezone' => false, 'starttime' => false, 'endtime' => false)); ?>
							</li>
						</ol>
					</div>
				</div>

				<div class="es-list-item__state">
					<div class="es-label-state es-label-state--featured" data-original-title="<?php echo JText::_('COM_ES_EVENTS_FEATURED_EVENTS');?>" data-es-provide="tooltip">
						<i class="es-label-state__icon"></i>
					</div>
				</div>

				<div class="es-list-item__action">
					<div class="o-btn-group">
						<?php echo $this->html('event.action', $event); ?>
					</div>

					<?php if (!$this->isMobile()) { ?>
						<?php echo $this->html('event.bookmark', $event); ?>
					<?php } ?>
				</div>
			</div>

			<?php if (!$this->isMobile()) { ?>
			<div class="es-list-item__bd">
				<div class="es-list-item__desc">
					<?php echo $this->html('string.truncate', $event->getDescription(), 200, '', false, false, false, true);?>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
</div>
