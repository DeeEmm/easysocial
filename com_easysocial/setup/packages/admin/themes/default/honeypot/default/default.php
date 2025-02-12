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

	<div id="pointsTable" class="panel-table">
		<table class="app-table table">
			<thead>
				<th width="1%" class="center">
					<?php echo $this->html('grid.checkAll'); ?>
				</th>

				<th>
					<?php echo JText::_('COM_ES_TABLE_COLUMN_DATA');?>
				</th>

				<th width="5%" class="center">
					<?php echo JText::_('COM_ES_TABLE_COLUMN_EVENT');?>
				</th>

				<th width="10%" class="center">
					<?php echo JText::_('COM_ES_TABLE_COLUMN_KEY');?>
				</th>

				<th width="15%" class="center">
					<?php echo $this->html('grid.sort', 'created', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'), $ordering, $direction); ?>
				</th>

				<th width="5%" class="center">
					<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering , $direction); ?>
				</th>
			</thead>

			<tbody>
			<?php if ($logs) { ?>
				<?php $i = 0; ?>
				<?php foreach ($logs as $log) { ?>
				<tr>
					<td class="center">
						<?php echo $this->html('grid.id', $i, $log->id); ?>
					</td>

					<td>
						<a href="javascript:void(0);" data-view data-id="<?php echo $log->id;?>"><?php echo JText::_('COM_ES_VIEW_DATA');?></a>
					</td>

					<td class="center">
						<?php echo ucfirst($log->type);?>
					</td>

					<td class="center">
						<?php echo $log->key;?>
					</td>

					<td class="center">
						<?php echo $log->created;?>
					</td>

					<td class="center">
						<?php echo $log->id;?>
					</td>
				</tr>
				<?php } ?>
			<?php } else { ?>
				<tr class="is-empty">
					<td colspan="6" class="empty center">
						<div><?php echo JText::_('COM_ES_HONEYPOT_EMPTY'); ?></div>
					</td>
				</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="<?php echo ($this->tmpl != 'component' ) ? 7 : 5; ?>">
						<div class="footer-pagination"><?php echo $pagination->getListFooter();?></div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
	<?php echo $this->html('form.action', 'honeypot'); ?>
</form>
