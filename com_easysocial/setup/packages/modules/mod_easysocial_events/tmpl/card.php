<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-event <?php echo $lib->getSuffix();?>">
	<?php foreach ($events as $event) { ?>
	<div class="mod-card">
		<div class="mod-card__cover-wrap">
			<div style="
				background-image : url(<?php echo $event->getCover($coverSize)?>);
				background-position : <?php echo $event->getCoverPosition();?>"
				class="mod-card__cover">
			</div>
		</div>
		<div class="mod-card__context">
			<div class="mod-card__avatar-holder">
				<div class="mod-card__calendar-date <?php echo ES::config()->get('layout.avatar.style') == 'rounded' ? 'es-card__calendar-date--rounded' : '';?>">
					<div class="mod-card__calendar-day">
						<?php echo $event->getEventStart()->format('d', true, true, false);?>
					</div>
					<div class="mod-card__calendar-mth">
						<?php echo $event->getEventStart()->format('M', true, true, false);?>
					</div>
				</div>
			</div>
			<a class="mod-card__title" href="<?php echo $event->getPermalink();?>"><?php echo $event->getName();?></a>
			<div class="mod-card__meta"><?php echo $event->getStartEndDisplay(array('end' => false)); ?></div>
			<?php if ($params->get('display_category' , true)) { ?>
				<div>
					<a href="<?php echo $event->getCategory()->getPermalink(); ?>"><?php echo $event->getCategory()->getTitle(); ?></a>
				</div>
			<?php } ?>
		</div>

		<div class="mod-card__ft mod-card--border">
			<div class="mod-card__meta">
				<ol class="g-list-inline g-list-inline--delimited">
					<li>
						<i class="fa fa-user"></i>&nbsp;<a href="<?php echo $event->getOwner()->getPermalink();?>"><?php echo $event->getOwner()->getName();?></a>
					</li>
					<?php if ($params->get('display_member_counter', true)) { ?>
					<li>
						<a data-original-title="<?php echo JText::sprintf(ES::string()->computeNoun('MOD_EASYSOCIAL_EVENTS_GUEST_COUNT', $event->getTotalGuests()), $event->getTotalGuests());?>"
						data-es-provide="tooltip" href="<?php echo $event->getAppPermalink('guests');?>">
							<i class="fa fa-users"></i>&nbsp;<?php echo JText::sprintf(ES::string()->computeNoun('MOD_EASYSOCIAL_EVENTS_GUEST_COUNT', $event->getTotalGuests()), $event->getTotalGuests());?>
						</a>
					</li>
					<?php } ?>

					<?php if ($params->get('display_rsvp', true)) { ?>
					<li class="t-lg-pull-right">
						<?php echo ES::themes()->html('event.action', $event, 'right'); ?>
					</li>
					<?php } ?>
				</ol>
			</div>
		</div>
	</div>
	<?php } ?>
	<?php if ($params->get('display_alllink', true)) { ?>
	<div class="mod-es-action">
		<a href="<?php echo ESR::events();?>" class="btn btn-es-default-o btn-sm btn-block"><?php echo JText::_('MOD_EASYSOCIAL_EVENTS_ALL_EVENT');?></a>
	</div>
	<?php } ?>
</div>
