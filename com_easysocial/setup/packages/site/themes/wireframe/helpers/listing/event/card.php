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
<div class="es-cards__item" data-item data-id="<?php echo $event->id;?>">
	<div class="es-card <?php echo $event->isFeatured() ? ' is-featured' : '';?> <?php echo $event->isPassed() ? ' is-passed' : '';?>">
		<div class="es-card__hd">
			<div class="es-card__action-group">
				<?php if ($event->canAccessActionMenu()) { ?>
				<div class="es-card__admin-action">
					<div class="pull-right dropdown_">
						<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown">
							<i class="fa fa-ellipsis-h"></i>
						</a>
						<ul class="dropdown-menu">
							<?php echo $this->html('event.adminActions', $event); ?>

							<?php $reportHtml = $this->html('event.report', $event); ?>

							<?php if ($reportHtml) { ?>
							<li>
								<?php echo $reportHtml; ?>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php } ?>
			</div>

			<?php echo $this->html('card.cover', $event); ?>
		</div>

		<div class="es-card__bd es-card--border has-label-state">
			<?php echo $this->html('card.calendar', $event->getEventStart()->format('M', true), $event->getEventStart()->format('d', true), $event->isPassed()); ?>

			<?php echo $this->html('card.icon', 'featured', 'COM_EASYSOCIAL_EVENTS_FEATURED_EVENT'); ?>

			<?php echo $this->html('card.title', $event); ?>

			<div class="es-card__meta t-lg-mb--sm">
				<ol class="g-list-inline g-list-inline--delimited">
					<li>
						<i class="fa fa-folder"></i>&nbsp; <a href="<?php echo $event->getCategory()->getFilterPermalink();?>"><?php echo $event->getCategory()->getTitle();?></a>
					</li>

					<li>
						<?php echo $this->html('event.type', $event); ?>
					</li>

					<?php if ($this->config->get('events.layout.seatsleft', true) && $event->seatsLeft() >= 0) { ?>
					<li data-es-provide="tooltip" data-original-title="<?php echo JText::sprintf('COM_EASYSOCIAL_EVENTS_REMAINING_SEATS_SHORT', $event->seatsLeft()); ?>">
						<i class="fa fa-ticket"></i>&nbsp; <?php echo $event->seatsLeft();?> / <?php echo $event->getTotalSeats();?>
					</li>
					<?php } ?>
				</ol>
			</div>

			<div class="es-card__meta t-lg-mb--sm">
				<ol class="g-list-inline g-list-inline--delimited">
					<li>
						<i class="far fa-calendar-alt"></i>&nbsp; <?php echo $event->getStartEndDisplay(); ?>
					</li>

					<?php if ($event->isRecurringEvent()) { ?>
					<li>
						<?php echo JText::_('COM_EASYSOCIAL_EVENTS_RECURRING_EVENT'); ?>
					</li>
					<?php } ?>
				</ol>
			</div>

			<?php if ($this->config->get('events.layout.listingsdesc')) { ?>
				<div class="es-card__meta">
					<?php if ($event->description) { ?>
						<?php echo $this->html('string.truncate', $event->getDescription(), 200, '', false, false, false, true);?>
					<?php } else { ?>
						<?php echo JText::_('COM_EASYSOCIAL_GROUPS_NO_DESCRIPTION_YET'); ?>
					<?php }?>
				</div>
			<?php } ?>
		</div>
		<div class="es-card__ft es-card--border">
			<div class="es-card__meta">
				<ol class="g-list-inline g-list-inline--delimited">
					<li>
						<a href="<?php echo $event->getAppPermalink('guests');?>" data-es-provide="tooltip"
							data-original-title="<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_EVENTS_GUESTS', $event->getTotalGoing()), $event->getTotalGoing()); ?>"
						>
							<i class="fa fa-users"></i>&nbsp; <?php echo $event->getTotalGoing();?>
						</a>
					</li>

					<?php if ($this->config->get('events.layout.address') && !empty($event->address)) { ?>
					<li>
						<a href="<?php echo $event->getAddressLink(); ?>" target="_blank"><i class="fa fa-map-marker-alt"></i>&nbsp; <?php echo ESJString::substr($event->address, 0, 15) . JText::_('COM_EASYSOCIAL_ELLIPSES'); ?></a>
					</li>
					<?php } ?>

					<?php if (!empty($showDistance)) { ?>
					<li>
						<i class="fa fa-compass"></i> <?php echo JText::sprintf('COM_EASYSOCIAL_EVENTS_EVENT_DISTANCE_AWAY', $event->distance, $this->config->get('general.location.proximity.unit', 'mile')); ?>
					</li>
					<?php } ?>

					<li class="pull-right">
						<?php echo $this->html('event.action', $event); ?>
					</li>
				</ol>
			</div>
	   </div>
	</div>
</div>
