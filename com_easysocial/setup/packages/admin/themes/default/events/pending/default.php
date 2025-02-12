<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<form action="index.php" method="post" name="adminForm" class="esForm" id="adminForm" data-table-grid>
	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
		</div>

		<?php if($this->tmpl != 'component'){ ?>
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<select class="o-form-control" name="type" id="filterType" data-table-grid-filter>
					<option value="all"<?php echo $type == 'all' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_FILTER_EVENT_TYPE'); ?></option>
					<option value="1"<?php echo $type === 1 ? ' selected="selected"' : '';?>><?php echo JText::_('COM_ES_CLUSTER_TYPE_PUBLIC'); ?></option>
					<option value="2"<?php echo $type === 2 ? ' selected="selected"' : '';?>><?php echo JText::_('COM_ES_CLUSTER_TYPE_PRIVATE'); ?></option>
					<option value="3"<?php echo $type === 3 ? ' selected="selected"' : '';?>><?php echo JText::_('COM_ES_CLUSTER_TYPE_INVITE_ONLY'); ?></option>
				</select>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--empty"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit' , $limit); ?>
			</div>
		</div>
		<?php } ?>
	</div>

	<div class="panel-table">
		<table class="app-table table">
			<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>

					<th>
						<?php echo $this->html('grid.sort', 'a.title', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'), $ordering, $direction); ?>
					</th>

					<?php if (!$callback) { ?>
					<th width="15%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ACTIONS'); ?>
					</th>
					<?php } ?>

					<th width="5%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_DRAFT'); ?>
					</th>

					<th width="5%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_USERS'); ?>
					</th>

					<th class="center" width="10%">
						<?php echo $this->html('grid.sort', 'b.title', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CATEGORY'), $ordering, $direction); ?>
					</th>

					<th class="center" width="10%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TYPE');?>
					</th>

					<th class="center" width="10%">
						<?php echo $this->html('grid.sort', 'a.created_by', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED_BY'), $ordering, $direction); ?>
					</th>

					<th class="center" width="10%">
						<?php echo $this->html('grid.sort', 'a.created', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'), $ordering, $direction); ?>
					</th>

					<th width="1%" class="center">
						<?php echo $this->html('grid.sort', 'a.id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering, $direction); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if (!empty($events)) { ?>
				<?php $i = 0;?>
				<?php foreach ($events as $event) { ?>
					<tr class="row<?php echo $i; ?>" data-grid-row data-id="<?php echo $event->id; ?>">
						<td align="center">
							<?php echo $this->html('grid.id', $i, $event->id); ?>
						</td>

						<td>
							<a href="<?php echo ESR::url(array('view' => 'events', 'layout' => 'form', 'id' => $event->id));?>"><?php echo JText::_($event->title); ?></a>
						</td>

						<?php if (!$callback) { ?>
						<td class="center">
							<a href="javascript:void(0);" class="btn btn-sm btn-es-primary-o" data-pending-approve>
								<?php echo JText::_('COM_EASYSOCIAL_USER_APPROVE_BUTTON'); ?>
							</a>

							<a href="javascript:void(0);" class="btn btn-sm btn-es-danger-o" data-pending-reject>
								<?php echo JText::_('COM_EASYSOCIAL_USER_REJECT_BUTTON'); ?>
							</a>
						</td>
						<?php } ?>

						<td class="center">
							<?php if ($event->isDraft()) { ?>
							<i class="fa fa-info-circle t-text--info" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_CLUSTERS_ITEM_IN_DRAFT_STATE'); ?>" data-es-provide="tooltip" style="font-size: 14px;"></i>
							<?php } else { ?>
							&mdash;
							<?php } ?>
						</td>

						<td class="center">
							<?php echo $event->getTotalGuests(); ?>
						</td>

						<td class="center">
							<a href="<?php echo ESR::url(array('view' => 'events', 'layout' => 'category', 'id' => $event->category_id)); ?>" target="_blank"><?php echo JText::_($event->getCategory()->title); ?></a>
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
							<a href="<?php echo FRoute::url(array('view' => 'users', 'layout' => 'form', 'id' => $event->getCreator()->id)); ?>" target="_blank"><?php echo $event->getCreator()->getName(); ?></a>
						</td>

						<td class="center">
							<?php echo $event->created; ?>
						</td>

						<td class="center">
							<?php echo $event->id;?>
						</td>
					</tr>
				<?php $i++; ?>
				<?php } ?>
			<?php } else { ?>
				<tr class="is-empty">
					<td colspan="10" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_EVENTS_NO_EVENT_FOUND');?>
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

	<?php echo $this->html('form.action', 'events', '', 'events'); ?>
	<input type="hidden" name="layout" value="pending" />
	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
</form>
