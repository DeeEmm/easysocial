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
<div id="es" class="mod-es mod-es-sidebar-events <?php echo $this->lib->getSuffix();?>" data-es-event-filters>
	<div class="es-sidebar" data-sidebar>

		<?php echo $this->lib->render('module', 'es-events-sidebar-top' , 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php if ((!$cluster && $user->isViewer() && $this->lib->my->canCreateEvents()) || ($cluster && $cluster->canCreateEvent())) { ?>
		<a href="<?php echo ESR::events($createUrl); ?>" class="btn btn-es-primary btn-create btn-block t-lg-mb--xl">
			<?php echo JText::_('COM_EASYSOCIAL_EVENTS_CREATE_EVENT'); ?>
		</a>
		<?php } ?>

		<div class="es-side-widget">

			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_EVENTS'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<?php if ($browseView) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'all' && !$activeCategory ? 'active' : ''; ?>" data-filter-item data-type="all">
							<a href="<?php echo $filtersLink->all; ?>"
							title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_ALL', true); ?>"
							class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_ALL'); ?>
							</a>

							<span class="o-tabs__bubble" data-counter><?php echo $counters->all; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>

						<li class="o-tabs__item has-notice <?php echo $filter == 'featured' ? 'active' : ''; ?>" data-filter-item data-type="featured">
							<a href="<?php echo $filtersLink->featured; ?>"
							title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_FEATURED', true); ?>"
							class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_FEATURED'); ?>
							</a>

							<span class="o-tabs__bubble" data-counter><?php echo $counters->featured; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
					<?php } else { ?>
						<?php if ((!$cluster && $user->canCreateEvents()) || ($cluster && $cluster->canCreateEvent())) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'created'? 'active' : ''; ?>" data-filter-item data-type="created">
							<a href="<?php echo $filtersLink->created; ?>"

							title="<?php echo JText::_('COM_ES_CREATED_EVENTS', true); ?>"
							class="o-tabs__link">
								<?php echo JText::_('COM_ES_CREATED_EVENTS'); ?>
							</a>

							<span class="o-tabs__bubble" data-counter><?php echo $counters->created; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
						<?php } ?>

						<?php if (!$cluster) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'participated'? 'active' : ''; ?>" data-filter-item data-type="participated">
							<a href="<?php echo $filtersLink->participated; ?>"

							title="<?php echo JText::_('COM_ES_PARTICIPATED_EVENTS', true); ?>"
							class="o-tabs__link">
								<?php echo JText::_('COM_ES_PARTICIPATED_EVENTS'); ?>
							</a>

							<span class="o-tabs__bubble" data-counter><?php echo $counters->participated; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
						<?php } ?>
					<?php } ?>

					<?php if ($showMyEvents) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'mine' ? 'active' : ''; ?>" data-filter-item data-type="mine">
							<a href="<?php echo ES::event()->getFilterPermalink(array('filter' => 'mine', 'cluster' => $cluster)); ?>"
							title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_MINE', true); ?>"
							class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_MINE'); ?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counters->created; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>

						<?php if ((!$cluster && $this->lib->my->canCreateEvents()) || ($cluster && $cluster->canCreateEvent())) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'createdbyme' ? 'active' : ''; ?>" data-filter-item data-type="createdbyme">
							<a href="<?php echo ES::event()->getFilterPermalink(array('filter' => 'createdbyme', 'cluster' => $cluster)); ?>"
							title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_CREATEDBYME', true); ?>"
							class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_CREATED_BY_ME'); ?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counters->createdbyme; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
						<?php } ?>

					<?php } ?>

					<?php if ($showPendingEvents) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'review' ? 'active' : '';?>" data-filter-item data-type="review">
							<a href="<?php echo $filtersLink->pending;?>"
							title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_REVIEW', true);?>"
							class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_REVIEW');?>
							</a>

							<span class="o-tabs__bubble" data-counter><?php echo $counters->totalPendingEvents;?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
					<?php } ?>

					<?php if ($showTotalInvites) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'invited' ? 'active' : ''; ?>" data-filter-item data-type="invited">
							<a href="<?php echo $filtersLink->invited; ?>"
							title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_INVITED', true); ?>"
							class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_INVITED'); ?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counters->invited; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
					<?php } ?>

					<?php if ($browseView) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'nearby' ? 'active' : ''; ?>" data-filter-item data-type="nearby">
							<a href="<?php echo ES::event()->getFilterPermalink(array('filter' => 'nearby', 'cluster' => $cluster)); ?>"
							title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_NEARBY', true); ?>"
							class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_NEARBY'); ?>
							</a>
							<div class="o-loader o-loader--sm"></div>
						</li>
					<?php } ?>

					<?php if ($browseView && $user->canCreateEvents()) { ?>
						<li class="o-tabs__item has-notice <?php echo $filter == 'unpublished' ? 'active' : ''; ?>" data-filter-item data-type="unpublished">
							<a href="<?php echo ES::event()->getFilterPermalink(array('filter' => 'unpublished', 'cluster' => $cluster)); ?>"
							title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_UNPUBLISHED', true); ?>"
							class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_UNPUBLISHED'); ?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counters->unpublished; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>

		<?php if ($browseView) { ?>
			<hr class="es-hr" />
			<div class="es-side-widget">
				<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_EVENTS_CALENDAR_WIDGET_TITLE'); ?>

				<div class="es-side-widget__bd">
					<div class="es-side-widget-events-calendar" data-events-calendar-wrapper>
						<?php echo $this->lib->html('html.loading'); ?>
						<div data-events-calendar></div>
					</div>
				</div>
			</div>
		<?php } ?>

		<?php if ($browseView) { ?>
			<hr class="es-hr" />
			<div class="es-side-widget">
				<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_EVENTS_FILTER_BY_DATE_SIDEBAR_TITLE'); ?>

				<div class="es-side-widget__bd">
					<ul class="o-tabs o-tabs--stacked">
						<li class="o-tabs__item has-notice <?php echo $filter == 'date' && $activeDateFilter == 'today' ? 'active' : ''; ?>" data-filter-item data-type="today">
							<a href="<?php echo $dateLinks->today; ?>" class="o-tabs__link" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_TODAY'); ?>">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_TODAY'); ?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counters->today; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
						<li class="o-tabs__item has-notice <?php echo $filter == 'date' && $activeDateFilter == 'tomorrow' ? 'active' : ''; ?>" data-filter-item data-type="tomorrow">
							<a href="<?php echo $dateLinks->tomorrow; ?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_TOMORROW'); ?>" class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_TOMORROW'); ?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counters->tomorrow; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>

						<li class="o-tabs__item has-notice <?php echo $filter == 'week1' ? 'active' : ''; ?>" data-filter-item data-type="week1">
							<a href="<?php echo ES::event()->getFilterPermalink(array('filter' => 'week1', 'cluster' => $cluster)); ?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_UPCOMING_1WEEK', true); ?>" class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_WEEK1'); ?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counters->week1; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>

						<li class="o-tabs__item has-notice <?php echo $filter == 'week2' ? 'active' : ''; ?>" data-filter-item data-type="week2">
							<a href="<?php echo ES::event()->getFilterPermalink(array('filter' => 'week2', 'cluster' => $cluster)); ?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_UPCOMING_2WEEK', true); ?>" class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_WEEK2'); ?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counters->week2; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>

						<li class="o-tabs__item has-notice <?php echo $filter == 'date' && $activeDateFilter == 'month' ? 'active' : ''; ?>" data-filter-item data-type="month">
							<a href="<?php echo $dateLinks->month; ?>" class="o-tabs__link" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_MONTH', true); ?>">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_MONTH'); ?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counters->month; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
						<li class="o-tabs__item has-notice <?php echo $filter == 'date' && $activeDateFilter == 'year' ? 'active' : ''; ?>" data-filter-item data-type="year">
							<a href="<?php echo $dateLinks->year; ?>" class="o-tabs__link" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_YEAR', true); ?>">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_YEAR'); ?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counters->year; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
						<li class="o-tabs__item has-notice <?php echo $filter == 'past' ? 'active' : ''; ?>" data-filter-item data-type="past">
							<a class="o-tabs__link" href="<?php echo ES::event()->getFilterPermalink(array('filter' => 'past', 'cluster' => $cluster)); ?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_PAST', true); ?>">
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_FILTER_PAST'); ?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $counters->past; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
					</ul>
				</div>
			</div>

			<hr class="es-hr" />
			<div class="es-side-widget">
				<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_EVENTS_CATEGORIES_SIDEBAR_TITLE'); ?>

				<div class="es-side-widget__bd">
					<?php echo $this->lib->html('categories.sidebar', SOCIAL_TYPE_EVENT, $activeCategory) ?>
				</div>
			</div>
		<?php } ?>

		<?php echo $this->lib->render('module', 'es-events-sidebar-bottom' , 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
</div>

<script>
EasySocial
.require()
.script('site/events/filter')
.done(function($){
	// Apply controller on body level
	$('body').addController('EasySocial.Controller.Events.Filter');
});
</script>
