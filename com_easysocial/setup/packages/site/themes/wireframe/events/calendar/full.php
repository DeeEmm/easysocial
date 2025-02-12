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
<div data-es-calendar data-filter="<?php echo $filter; ?>" data-categoryid="<?php echo $categoryId; ?>" data-clusterid="<?php echo $clusterId; ?>">
	<div class="datepicker" style="display:block">
		<div class="datepicker-days" style="display: block;">
			<table class="es-calendar table-bordered">
				<tbody>
					<tr class="es-calendar-control">
						<td class="es-calendar-previous text-center" >
							<a class="btn-previous-month" href="javascript:void(0);" data-calendar-nav="<?php echo $calendar->previous; ?>">
								<i class="fa fa-chevron-left"></i>
							</a>
						</td>

						<td class="es-calendar-month text-center" colspan="5" data-month="<?php echo $calendar->year . '-' . $calendar->month; ?>">
							<div class="t-text--center">
								<a href="javascript:void(0);" class="t-text--muted">
									<i class="far fa-calendar-alt"></i>&nbsp; <?php echo $calendar->header;?>
								</a>
							</div>
						</td>

						<td class="es-calendar-next text-center" >
							<a class="btn-next-month" href="javascript:void(0);" data-calendar-nav="<?php echo $calendar->next; ?>">
								<i class="fa fa-chevron-right"></i>
							</a>
						</td>
					</tr>
					<tr class="es-calendar-days">
						<?php foreach ($weekdays as $dayTitle) { ?>
						<td class="text-center day-of-week">
							<?php echo $dayTitle; ?>
						</td>
						<?php } ?>
					</tr>
					<tr>
						<?php $current = 1; ?>
						<?php while ($calendar->blank) { ?>
							<td class="empty">
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
								<div onclick="void(0)"><!-- Quickfix make calendar tooltips able to show in touch devices -->
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

									<?php if (!empty($days[$dayNumber])) { ?>
									<b><?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_EVENTS', count($days[$dayNumber])), count($days[$dayNumber]));?></b>
									<div class="es-calendar-tooltips">
										<div class="es-calendar-tooltips__title">
											<a href="<?php echo ESR::events(array('filter' => 'date', 'date' => $calendarDate));?>"
												title="<?php echo JText::sprintf('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_DATE', ES::date($calendarDate)->format(JText::_('COM_EASYSOCIAL_DATE_DMY'))); ?>"
												data-route data-date="<?php echo $calendarDate; ?>">
												<?php echo ES::date($calendarDate, false)->format(JText::_('COM_EASYSOCIAL_DATE_DMY')); ?>
											</a>
										</div>

										<ul class="g-list-unstyled">
										<?php foreach ($days[$dayNumber] as $event) { ?>
											<li>
												<div class="o-media">
													<div class="o-media__image">
														<?php echo $this->html('avatar.mini', $event->getName(), $event->getPermalink(), $event->getAvatar(SOCIAL_AVATAR_SQUARE), 'sm', 't-lg-mb--lg'); ?>
													</div>
													<div class="o-media__body">
														<?php if ($event->isAllDay()) { ?>
														<div class="media-title">
															<a href="<?php echo $event->getPermalink(); ?>"><?php echo $event->getName(); ?></a>
														</div>
														<?php } else { ?>
														<div class="media-title">
															<a href="<?php echo $event->getPermalink(); ?>"><?php echo $event->getName(); ?></a>
														</div>
														<div class="media-time"><?php echo $event->getStartEndDisplay(array('startdate' => false)); ?></div>
														<?php } ?>
													</div>
												</div>
											</li>
										<?php } ?>
										</ul>
									</div>
									<?php } ?>

								</div>

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
							<td class="empty">
								<small class="other-day"></small>
							</td>
							<?php $current++; ?>
						<?php } ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
