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
<form action="<?php echo JRoute::_('index.php?option=com_easysocial&view=events');?>" method="post" name="adminForm" class="esForm" id="adminForm" data-table-grid>

	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search', $search); ?>
		</div>

		<?php if($this->tmpl != 'component'){ ?>
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.published', 'state', $state); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<select class="o-form-control" name="type" id="filterType" data-table-grid-filter>
					<option value="all"<?php echo $type == 'all' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_FILTER_EVENT_TYPE'); ?></option>
					<option value="1"<?php echo $type == 1 ? ' selected="selected"' : '';?>><?php echo JText::_('COM_ES_CLUSTER_TYPE_PUBLIC'); ?></option>
					<option value="2"<?php echo $type == 2 ? ' selected="selected"' : '';?>><?php echo JText::_('COM_ES_CLUSTER_TYPE_PRIVATE'); ?></option>
					<option value="3"<?php echo $type == 3 ? ' selected="selected"' : '';?>><?php echo JText::_('COM_ES_CLUSTER_TYPE_INVITE_ONLY'); ?></option>
				</select>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.clusterCategories', 'category', $category, 'event'); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--empty"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit', $limit); ?>
			</div>
		</div>
		<?php } ?>
	</div>

	<div class="panel-table">
		<table class="app-table table">
			<thead>
				<tr>
					<?php if ($multiple) { ?>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					<?php } ?>

					<th>
						<?php echo $this->html('grid.sort', 'a.title', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'), $ordering, $direction); ?>
					</th>

					<?php if ($this->tmpl != 'component') { ?>
					<th class="center" width="5%">
						<?php echo $this->html('grid.sort', 'a.featured', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_FEATURED'), $ordering, $direction); ?>
					</th>

					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'a.state', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATUS'), $ordering, $direction); ?>
					</th>

					<th class="center" width="15%">
						<?php echo $this->html('grid.sort', 'b.title', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CATEGORY'), $ordering, $direction); ?>
					</th>

					<th class="center" width="10%">
						<?php echo JText::_('COM_ES_TABLE_COLUMN_SCHEDULE'); ?>
					</th>

					<th class="center" width="8%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TYPE');?>
					</th>

					<th class="center" width="10%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED_BY'); ?>
					</th>

					<th width="5%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_USERS'); ?>
					</th>
					<?php } ?>

					<th class="center" width="10%">
						<?php echo $this->html('grid.sort', 'a.created', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'), $ordering, $direction); ?>
					</th>

					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'a.id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering, $direction); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if (!empty($events)) { ?>
				<?php $i = 0;?>
				<?php foreach ($events as $event) { ?>
					<tr class="row<?php echo $i; ?>" data-grid-row data-id="<?php echo $event->id; ?>">

						<?php if ($multiple) { ?>
						<td align="center">
							<?php echo $this->html('grid.id', $i, $event->id); ?>
						</td>
						<?php } ?>

						<td>
							<a href="<?php echo ESR::url(array('view' => 'events', 'layout' => 'form', 'id' => $event->id));?>"
								data-event-insert
								data-id="<?php echo $event->id;?>"
								data-avatar="<?php echo $event->getAvatar();?>"
								data-title="<?php echo $this->html('string.escape', $event->getName());?>"
								data-alias="<?php echo $event->getAlias();?>"
							>
								<?php echo JText::_($event->title); ?>
							</a>
						</td>

						<?php if ($this->tmpl != 'component') { ?>
						<td class="center">
							<?php echo $this->html('grid.featured', $event, 'events', 'featured'); ?>
						</td>

						<td class="center">
							<?php echo $this->html('grid.published', $event, 'events', 'state', array(2 => 'approve'), array(2 => 'COM_EASYSOCIAL_GRID_TOOLTIP_APPROVE_ITEM'), array(2 => 'pending')); ?>
						</td>

						<td class="center">
							<a href="<?php echo ESR::url(array('view' => 'events', 'layout' => 'category', 'id' => $event->category_id)); ?>" target="_blank"><?php echo JText::_($event->getCategory()->title); ?></a>
						</td>

						<td class="center">
							<?php if ($event->isOver()) { ?>
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_OVER_EVENT'); ?>
							<?php } ?>

							<?php if ($event->isOngoing()) { ?>
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_ONGOING_EVENT'); ?>
							<?php } ?>

							<?php if ($event->isUpcoming()) { ?>
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_UPCOMING_EVENT'); ?>
							<?php } ?>

							<?php if ($event->isRecurringEvent()) { ?>
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_RECURRING_EVENT'); ?>
							<?php } ?>
						</td>

						<td class="center">
							<?php if ($event->isGroupEvent()) { ?>
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_GROUP_EVENT'); ?>
							<?php } else if ($event->isPageEvent()) { ?>
								<?php echo JText::_('COM_EASYSOCIAL_EVENTS_PAGE_EVENT'); ?>
							<?php } else { ?>
								<?php if ($event->isOpen()){ ?>
									<?php echo JText::_('COM_ES_CLUSTER_TYPE_PUBLIC'); ?>
								<?php } ?>

								<?php if ($event->isClosed()){ ?>
									<?php echo JText::_('COM_ES_CLUSTER_TYPE_PRIVATE'); ?>
								<?php } ?>

								<?php if ($event->isInviteOnly()){ ?>
									<?php echo JText::_('COM_ES_CLUSTER_TYPE_INVITE_ONLY'); ?>
								<?php } ?>
							<?php } ?>
						</td>

						<td class="center">
							<a href="<?php echo ESR::url(array('view' => 'users', 'layout' => 'form', 'id' => $event->getCreator()->id)); ?>" target="_blank"><?php echo $event->getCreator()->getName(); ?></a>
						</td>

						<td class="center">
							<?php echo $event->getTotalGuests(); ?>
						</td>
						<?php } ?>

						<td class="center">
							<?php echo ES::date($event->created)->format(JText::_('DATE_FORMAT_LC4')); ?>
						</td>

						<td class="center">
							<?php echo $event->id;?>
						</td>
					</tr>
				<?php $i++; ?>
				<?php } ?>
			<?php } else { ?>
				<tr class="is-empty">
					<td colspan="10" class="center empty">
						<?php echo JText::_('COM_EASYSOCIAL_EVENTS_NO_EVENT_CREATED_YET');?>
					</td>
				</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="10" class="center">
						<div class="footer-pagination"><?php echo $pagination->getListFooter(); ?></div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
	<input type="hidden" name="boxchecked" value="0" data-table-grid-box-checked />
	<input type="hidden" name="task" value="" data-table-grid-task />
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="view" value="events" />
	<input type="hidden" name="controller" value="events" />
</form>

<?php if ($this->tmpl != 'component') { ?>
<div id="toolbar-actions" class="btn-wrapper t-hidden" data-toolbar-actions="others">
	<div class="dropdown">
		<button type="button" class="btn btn-small dropdown-toggle" data-toggle="dropdown">
			<span class="icon-cog"></span> <?php echo JText::_('Other Actions');?> &nbsp;<span class="caret"></span>
		</button>

		<ul class="dropdown-menu">
			<li>
				<a href="javascript:void(0);" data-action="switchOwner">
					<?php echo JText::_('COM_EASYSOCIAL_CHANGE_OWNER'); ?>
				</a>
			</li>
			<li class="divider">
			<li>
				<a href="javascript:void(0);" data-action="switchCategory">
					<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SWITCH_CATEGORY'); ?>
				</a>
			</li>
			<li class="divider">
			<li>
				<a href="javascript:void(0);" data-action="deletePastEvents">
					<?php echo JText::_('COM_ES_PURGE_PAST_EVENTS'); ?>
				</a>
			</li>
		</ul>
	</div>
</div>
<?php } ?>
