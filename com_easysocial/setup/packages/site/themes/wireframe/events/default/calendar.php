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
<div id="es" class="es-widget-calendar" data-es-calendar data-filter="<?php echo $filter; ?>" data-categoryid="<?php echo $categoryId; ?>" data-clusterid="<?php echo $clusterId; ?>">
	<div class="datepicker" style="display:block">
		<div class="datepicker-days" style="display: block;">
			<table class="table-condensed">
				<thead>
					<tr>
						<th class="prev" data-calendar-nav="<?php echo $calendar->previous; ?>">
							<i class="fa fa-chevron-left t-fs--sm"></i>
						</th>

						<th class="switch <?php echo !empty($events) ? 'has-events' : ''; ?>" colspan="5"
							data-month="<?php echo $calendar->year . '-' . $calendar->month; ?>">
							<div class="t-text--center">
								<a href="javascript:void(0);" class="t-text--muted">
									<i class="far fa-calendar-alt"></i>&nbsp; <?php echo $calendar->header;?>
								</a>
							</div>

							<div class="es-event-details">
								<div class="es-event-details__title">
									<a href="<?php echo ESR::events(array('filter' => 'date', 'date' => $calendar->year . '-' . $calendar->month));?>"
										title="<?php echo JText::sprintf('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_DATE', ES::date($calendar->year . '-' . $calendar->month . '-01', false)->format(JText::_('COM_EASYSOCIAL_DATE_MY', true))); ?>"
										data-route
										data-month="<?php echo $calendar->year . '-' . $calendar->month; ?>">
										<?php echo $calendar->header; ?>
									</a>
								</div>

								<ul class="g-list-unstyled">
								<?php foreach ($events as $event) { ?>
									<li>
										<div class="o-media">
											<div class="o-media__image o-media--top">
												<?php echo $this->html('avatar.mini', $event->getName(), $event->getPermalink(), $event->getAvatar(SOCIAL_AVATAR_SMALL), 'sm'); ?>
											</div>
											<div class="o-media__body">
												<div class="media-title">
													<a href="<?php echo $event->getPermalink(); ?>"><?php echo $event->getName(); ?></a>
												</div>
												<div class="media-time"><?php echo $event->getStartEndDisplay(array('end' => false)); ?></div>
											</div>
										</div>
									</li>
								<?php } ?>
								</ul>
							</div>
						</th>
						<th class="next" data-calendar-nav="<?php echo $calendar->next; ?>">
							<i class="fa fa-chevron-right t-fs--sm"></i>
						</th>
					</tr>
					<tr>
						<?php foreach ($weekdays as $dayTitle) { ?>
						<th class="dow">
							<?php echo $dayTitle; ?>
						</th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
					<tr>
						<?php $current = 1; ?>
						<?php while ($calendar->blank) { ?>
							<td class="day old">
								<small class="other-day"></small>
							</td>
							<?php $calendar->blank--;?>
							<?php $current++; ?>
						<?php } ?>

						<?php $dayNumber = 1; ?>


						<?php while ($dayNumber <= $calendar->days_in_month) { ?>
							<?php $dayNumberPadded = str_pad($dayNumber, 2, '0', STR_PAD_LEFT); ?>
							<?php $calendarDate = $calendar->year . '-' . $calendar->month . '-' . $dayNumberPadded; ?>
							<td class="day <?php if (!empty($days[$dayNumber])) { ?>has-events<?php }  echo $calendarDate == $today? ' is-today':'';?>" data-date="<?php echo $calendarDate; ?>">
								<div>
									<?php if ($calendarDate == $today) { ?>
									<a href="<?php echo ESR::events(array('filter' => 'date'));?>"
										title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_TODAY'); ?> - <?php echo ES::date($calendarDate, false)->format(JText::_('COM_EASYSOCIAL_DATE_DMY')); ?>"
										data-route><?php echo $dayNumber;?></a>
									<?php } else if ($calendarDate == $tomorrow) { ?>
									<a href="<?php echo ESR::events(array('filter' => 'date', 'date' => $calendarDate));?>"
										title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_TOMORROW'); ?> - <?php echo ES::date($calendarDate, false)->format(JText::_('COM_EASYSOCIAL_DATE_DMY')); ?>"
										data-route><?php echo $dayNumber;?></a>
									<?php } else { ?>
									<a href="<?php echo ESR::events(array('filter' => 'date', 'date' => $calendarDate));?>"
										title="<?php echo JText::sprintf('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_DATE', ES::date($calendarDate, false)->format(JText::_('COM_EASYSOCIAL_DATE_DMY'))); ?>"
										data-route><?php echo $dayNumber;?></a>

									<?php } ?>
								</div>
								<?php if (!empty($days[$dayNumber])) { ?>
								<div class="es-event-details">
									<div class="es-event-details__title">
										<a href="<?php echo ESR::events(array('filter' => 'date', 'date' => $calendarDate));?>" title="<?php echo JText::sprintf('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_DATE', ES::date($calendarDate)->format(JText::_('COM_EASYSOCIAL_DATE_DMY'))); ?>"
											data-route
											data-date="<?php echo $calendarDate; ?>">
											<?php echo ES::date($calendarDate, false)->format(JText::_('COM_EASYSOCIAL_DATE_DMY')); ?>
										</a>
									</div>
									<ul class="g-list-unstyled">
									<?php foreach ($days[$dayNumber] as $event) { ?>
										<li>
											<div class="o-flag">
												<div class="o-flag__image o-flag--top">
													<?php echo $this->html('avatar.mini', $event->getName(), $event->getPermalink(), $event->getAvatar(SOCIAL_AVATAR_SMALL), 'sm'); ?>
												</div>
												<div class="o-flag__body">
													<?php if ($event->isAllDay()) { ?>
													<div class="media-title">
														<a href="<?php echo $event->getPermalink(); ?>"><?php echo $event->getName(); ?></a>
													</div>
													<?php } else { ?>
													<div class="media-title">
														<a href="<?php echo $event->getPermalink(); ?>"><?php echo $event->getName(); ?></a>
													</div>
													<div class="media-time"><?php echo $event->getStartEndDisplay(); ?></div>
													<?php } ?>
												</div>
											</div>


										</li>
									<?php } ?>
									</ul>
								</div>
								<?php } ?>
							</td>
							<?php $dayNumber++; ?>
							<?php $current++; ?>

							<?php if ($current > 7) { ?>
							</tr>
							<tr>
								<?php $current = 1; ?>
							<?php } ?>
						<?php } ?>

						<?php while ($current > 1 && $current <= 7) { ?>
							<td class="day old">
							</td>
							<?php $current++; ?>
						<?php } ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
