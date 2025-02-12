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
<?php if (!empty($activeCategory) && $this->config->get('events.layout.categoryheaders')) { ?>
<div class="t-lg-mb--xl">
	<?php echo $this->html('miniheader.eventCategory', $activeCategory); ?>
</div>
<?php } ?>

<?php if ($showDateNavigation) { ?>
<div class="o-btn-group o-btn-group-justified es-btn-group-date t-lg-mb--xl">
	<a href="<?php echo ESR::events(array('filter' => 'date', 'date' => $navigation->previous));?>" title="<?php echo $navigation->previousPageTitle;?>" class="btn btn-es-default-o btn-sm" data-navigation-date="<?php echo $navigation->previous;?>">
		<i class="fa fa-chevron-left"></i>
	</a>

	<span class="btn btn-es-default-o btn-sm"><b>
		<?php if ($activeDateFilter == 'today') { ?>
			<?php echo JText::_('COM_EASYSOCIAL_EVENTS_TODAY'); ?> (<?php echo $activeDate->format(JText::_('COM_EASYSOCIAL_DATE_DMY')); ?>)
		<?php } ?>

		<?php if ($activeDateFilter == 'tomorrow') { ?>
			<?php echo JText::_('COM_EASYSOCIAL_EVENTS_TOMORROW'); ?> (<?php echo $activeDate->format(JText::_('COM_EASYSOCIAL_DATE_DMY')); ?>)
		<?php } ?>

		<?php if ($activeDateFilter == 'month') { ?>
			<?php echo $activeDate->format(JText::_('COM_EASYSOCIAL_DATE_MY')); ?>
		<?php } ?>

		<?php if ($activeDateFilter == 'year') { ?>
			<?php echo $activeDate->format(JText::_('COM_EASYSOCIAL_DATE_Y')); ?>
		<?php } ?>

		<?php if ($activeDateFilter == 'normal') { ?>
			<?php echo $activeDate->format(JText::_('COM_EASYSOCIAL_DATE_DMY')); ?>
		<?php } ?>
		</b>
	</span>

	<a href="<?php echo ESR::events(array('filter' => 'date', 'date' => $navigation->next));?>" title="<?php echo $navigation->nextPageTitle;?>" class="btn btn-es-default-o btn-sm" data-navigation-date="<?php echo $navigation->next;?>">
		<i class="fa fa-chevron-right"></i>
	</a>
</div>
<?php } ?>

<?php if (!empty($featuredEvents)) { ?>
	<div class="es-snackbar2">
		<div class="es-snackbar2__context">
			<div class="es-snackbar2__title">
				<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_FEATURED');?>
			</div>
		</div>
	</div>

	<div class="<?php echo $this->isMobile() ? 'es-list' : 'es-cards es-cards--2';?>">
		<?php foreach ($featuredEvents as $event) { ?>
			<?php echo $this->html('listing.event', $event, array(
					'showDistance' => $showDistance,
					'isGroupOwner' => isset($isGroupOwner) ? $isGroupOwner : false,
					'style' => $this->isMobile() ? 'listing' : 'card',
					'browseView' => $browseView
				)); ?>
		<?php } ?>
	</div>
<?php } ?>

<div>

	<?php if (!$delayed) { ?>
	<div class="es-snackbar2">
		<div class="es-snackbar2__context">
			<div class="es-snackbar2__title">
				<?php echo JText::_($title);?>
			</div>
		</div>

		<?php $showHideRepetitionFilter = isset($showHideRepetitionFilter) ? $showHideRepetitionFilter : false; ?>

		<?php if ($showPastFilter || $showHideRepetitionFilter || $showSorting || $showDistanceSorting) { ?>
		<div class="es-snackbar2__actions">

			<?php if ($showDistanceSorting) { ?>
				<?php echo $this->html('form.popdown', 'radius', $distance, [
					$this->html('form.popdownOption', '1', '1 ' . $distanceUnit, '', false, array('data-radius="1"'), ''),
					$this->html('form.popdownOption', '5', '5 ' . $distanceUnit, '', false, array('data-radius="5"'), ''),
					$this->html('form.popdownOption', '10', '10 ' . $distanceUnit, '', false, array('data-radius="10"'), ''),
					$this->html('form.popdownOption', '25', '25 ' . $distanceUnit, '', false, array('data-radius="25"'), ''),
					$this->html('form.popdownOption', '50', '50 ' . $distanceUnit, '', false, array('data-radius="50"'), ''),
					$this->html('form.popdownOption', '100', '100 ' . $distanceUnit, '', false, array('data-radius="100"'), ''),
					$this->html('form.popdownOption', '200', '200 ' . $distanceUnit, '', false, array('data-radius="200"'), ''),
					$this->html('form.popdownOption', '300', '300 ' . $distanceUnit, '', false, array('data-radius="300"'), ''),
					$this->html('form.popdownOption', '400', '400 ' . $distanceUnit, '', false, array('data-radius="400"'), ''),
					$this->html('form.popdownOption', '500', '500 ' . $distanceUnit, '', false, array('data-radius="500"'), '')
				], 'left'); ?>
			<?php } ?>


			<?php if ($showSorting || $showDistanceSorting) { ?>
				<?php
					$sortOptions = array();

					// render the data attribute for the sorting title
					$closestSort = $helper->renderTitleAttribute($filter, $activeCategory, 'start', $fromAjax);
					$recentSort = $helper->renderTitleAttribute($filter, $activeCategory, 'recent', $fromAjax);

					$url = $sortingUrls['start'];
					$sortOptions[] = $this->html('form.popdownOption', 'start', 'COM_ES_SORT_NEAREST_DATE', '', false, array('data-ordering="start"', $closestSort), $url);

					if ($showSorting) {

						$url = $sortingUrls['recent'];
						$sortOptions[] = $this->html('form.popdownOption', 'recent', 'COM_ES_SORT_BY_RECENTLY_ADDED', '', false, array('data-ordering="recent"', $recentSort), $url);
					}

					if ($showDistanceSorting) {

						$url = $sortingUrls['distance'];
						$filterAtt = 'data-filter="' . $activeCategory ? 'category' : $filter . '"';
						$catAtt = 'data-categoryid="' . isset($activeCategory) && $activeCategory ? $activeCategory->id : '' . '"';
						$sortOptions[] = $this->html('form.popdownOption', 'distance', 'COM_EASYSOCIAL_EVENTS_SORTING_EVENT_DISTANCE', '', false, array('data-ordering="distance"', $filterAtt, $catAtt), $url);
					}
				?>
				<div class="<?php echo $includePast ? ' t-hidden' : ''; ?>" data-event-sorting>
					<?php echo $this->html('form.popdown', 'ordering', $ordering, $sortOptions); ?>
				</div>
			<?php } ?>

			<?php if ($showPastFilter || $showHideRepetitionFilter) { ?>
			<div class="dropdown_">
				<button type="button" class="btn btn-sm btn-es-default-o dropdown-toggle_" data-es-toggle="dropdown" aria-expanded="false">
					<i class="fa fa-sliders-h"></i>
				</button>
				<div class="dropdown-menu dropdown-menu-right dropdown-menu--snackbar-action">
					<div class="t-lg-pl--lg t-lg-pr--lg">
						<?php if ($showPastFilter) { ?>
						<div class="o-form-group t-xs-ml--md">
							<div class="o-checkbox">
								<input type="checkbox" id="es-show-past-event" class="t-lg-pull-right" <?php echo $includePast ? 'checked="checked"' : '';?> data-events-past>

								<label for="es-show-past-event">
									<a href="<?php echo $sortingUrls['current']['past']; ?>" data-include-past-link>
										<?php echo JText::_('COM_EASYSOCIAL_EVENTS_INCLUDE_PAST_EVENTS'); ?>
									</a>
								</label>
							</div>
						</div>
						<?php } ?>

						<?php if ($showHideRepetitionFilter) { ?>
						<div class="o-form-group t-xs-ml--md">
							<div class="o-checkbox">
								<input type="checkbox" id="es-hide-repetition-event" class="t-lg-pull-right" <?php echo $hideRepetition ? 'checked="checked"' : '';?> data-events-repetition>

								<label for="es-hide-repetition-event">
									<a href="<?php echo $sortingUrls['current']['repetition']; ?>" data-hide-repetition-link>
										<?php echo JText::_('COM_ES_EVENTS_HIDE_REPETITIVE_EVENTS'); ?>
									</a>
								</label>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
	<?php } ?>

	<div class="o-grid">



	</div>

	<div class="es-list-result" data-sub-wrapper>

		<?php echo $this->html('listing.loader', 'card', 4, 2, array('snackbar' => false)); ?>

		<div data-events-list>
			<?php echo $this->includeTemplate('site/events/default/items'); ?>
		</div>
	</div>
</div>
