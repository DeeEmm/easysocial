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
<form action="index.php" method="post" name="adminForm" class="esForm" id="adminForm" data-table-grid>
	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
		</div>

		<?php if ($this->tmpl != 'component') { ?>

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

					<th class="center" width="10%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CATEGORY'); ?>
					</th>

					<th class="center" width="10%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED_BY'); ?>
					</th>

					<th class="center" width="10%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'); ?>
					</th>

					<th width="1%" class="center">
						<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering, $direction); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if (!empty($listings)) { ?>
				<?php $i = 0;?>
				<?php foreach ($listings as $listing) { ?>
					<tr class="row<?php echo $i; ?>" data-grid-row data-id="<?php echo $listing->id; ?>">
						<td align="center">
							<?php echo $this->html('grid.id', $i, $listing->id); ?>
						</td>

						<td>
							<a href="<?php echo ESR::url(array('view' => 'marketplaces', 'layout' => 'form', 'id' => $listing->id));?>"><?php echo JText::_($listing->title); ?></a>
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
							<?php if ($listing->isDraft()) { ?>
							<i class="fa fa-info-circle t-text--info" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_CLUSTERS_ITEM_IN_DRAFT_STATE'); ?>" data-es-provide="tooltip" style="font-size: 14px;"></i>
							<?php } else { ?>
							&mdash;
							<?php } ?>
						</td>

						<td class="center">
							<a href="<?php echo ESR::url(array('view' => 'marketplaces', 'layout' => 'category', 'id' => $listing->category_id)); ?>" target="_blank"><?php echo JText::_($listing->getCategory()->title); ?></a>
						</td>

						<td class="center">
							<a href="<?php echo FRoute::url(array('view' => 'users', 'layout' => 'form', 'id' => $listing->getCreator()->id)); ?>" target="_blank"><?php echo $listing->getCreator()->getName(); ?></a>
						</td>

						<td class="center">
							<?php echo $listing->created; ?>
						</td>

						<td class="center">
							<?php echo $listing->id;?>
						</td>
					</tr>
				<?php $i++; ?>
				<?php } ?>
			<?php } else { ?>
				<tr class="is-empty">
					<td colspan="10" class="center">
						<?php echo JText::_('COM_ES_MARKETPLACES_EMPTY_PENDING_MESSAGE');?>
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

	<?php echo $this->html('form.action', 'marketplaces', '', 'marketplaces'); ?>
	<input type="hidden" name="layout" value="pending" />
	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
</form>
