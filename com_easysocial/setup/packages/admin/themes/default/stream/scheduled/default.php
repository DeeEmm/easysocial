<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<form name="adminForm" id="adminForm" method="post" data-table-grid>

	<div class="app-filter-bar">

		<div class="app-filter-bar__cell app-filter-bar__cell--empty"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--empty"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit' , $limit); ?>
			</div>
		</div>
	</div>

	<div class="panel-table">
		<table class="app-table table" data-stream-list>
			<thead>
				<tr>
					<th width="1%">
						<input type="checkbox" name="toggle" class="checkAll" data-table-grid-checkall />
					</th>
					<th width="10%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_STREAM_TITLE_ACTOR'); ?>
					</th>
					<th width="15%">
						<?php echo JText::_('COM_EASYSOCIAL_STREAM_TITLE_ACTION'); ?>
					</th>
					<th>
						<?php echo JText::_('COM_EASYSOCIAL_STREAM_TITLE_CONTENT'); ?>
					</th>
					<th width="10%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_STREAM_TITLE_TYPE'); ?>
					</th>
					<th width="10%" class="center">
						<?php echo $this->html('grid.sort' , 'created' , JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED') , $ordering , $direction); ?>
					</th>
					<th width="10%" class="center">
						<?php echo $this->html('grid.sort' , 'scheduledModified' , JText::_('COM_EASYSOCIAL_TABLE_COLUMN_MODIFIED') , $ordering , $direction); ?>
					</th>
					<th width="10%" class="center">
						<?php echo $this->html('grid.sort' , 'scheduledDate' , JText::_('COM_ES_TABLE_COLUMN_SCHEDULE') , $ordering , $direction); ?>
					</th>
					<th width="5%" class="center">
						<?php echo $this->html('grid.sort' , 'id' , JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID') , $ordering , $direction); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($items) { ?>

					<?php $i = 0; ?>
					<?php foreach ($items as $item) { ?>
					<tr data-stream-item data-id="<?php echo $item->id;?>">
						<td class="center">
							<?php echo $this->html('grid.id' , $i , $item->id); ?>
						</td>
						<td class="center">
							<?php echo $item->actorName; ?>
						</td>
						<td>
							<?php
								$itemVerb = str_replace('.', '_', $item->verb);
								$jtext = strtoupper('COM_EASYSOCIAL_STREAM_' . $item->context_type . '_' . $itemVerb);
							?>
							<?php echo JText::_($jtext); ?>
						</td>
						<td>
							<?php echo ($item->content) ? $this->html('string.truncater', $item->content, 60) : 'N/A'; ?>
						</td>
						<td class="center">
							<?php $cluster = ($item->cluster_id) ? $item->clusterName . ' ('. $item->cluster_type .')' : SOCIAL_STREAM_ACTOR_TYPE_USER . '.' . $item->context_type; ?>
							<?php echo $cluster; ?>
						</td>
						<td class="center">
							<?php echo ES::date($item->created)->toSql(); ?>
						</td>
						<td class="center">
							<?php echo ES::date($item->scheduledModified)->toSql(); ?>
						</td>
						<td class="center">
							<?php echo ES::date($item->scheduledDate)->toSql(); ?>
						</td>
						<td class="center">
							<?php echo $item->id; ?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>

				<?php } else { ?>
				<tr class="is-empty">
					<td colspan="8" class="empty">
						<?php echo JText::_('COM_EASYSOCIAL_STREAM_NO_ITEM_FOUND'); ?>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="8">
						<div class="footer-pagination">
						<?php echo $pagination->getListFooter(); ?>
						</div>
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
	<input type="hidden" name="view" value="stream" />
	<input type="hidden" name="controller" value="stream" />
</form>
