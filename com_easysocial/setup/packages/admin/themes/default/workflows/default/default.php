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
<form name="adminForm" id="adminForm" method="post" action="index.php?option=com_easysocial&view=<?php echo $view;?>&layout=workflows" data-table-grid>

	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search', $search); ?>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--empty"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit' , $limit); ?>
			</div>
		</div>
	</div>

	<div id="workflowsTable" class="panel-table" data-workflow>
		<table class="app-table table">
			<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>

					<th>
						<?php echo $this->html('grid.sort', 'title', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'), $ordering, $direction); ?>
					</th>

					<th width="10%" class="center">
						<?php echo JText::_('COM_ES_TABLE_COLUMN_ITEMS'); ?>
					</th>

					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering, $direction); ?>
					</th>
				</tr>
			</thead>

			<tbody>
			<?php if ($workflows) { ?>
				<?php $i = 0; ?>
				<?php foreach ($workflows as $workflow) { ?>
				<tr data-workflow-item data-title="<?php echo $this->html('string.escape', $workflow->title);?>" data-id="<?php echo $workflow->id;?>">
					<td>
						<?php echo $this->html('grid.id', $i, $workflows[$i]->id); ?>
					</td>

					<td style="text-align:left;">
						<a href="<?php echo ESR::_('index.php?option=com_easysocial&view=workflows&layout=form&id=' . $workflow->id); ?>">
							<?php echo JText::_($workflow->getTitle()); ?>
						</a>
					</td>

					<td class="center">
						<?php echo $workflow->getTotalItems(); ?>
					</td>

					<td class="center">
						<?php echo $workflow->id;?>
					</td>
				</tr>
				<?php $i++; ?>
				<?php } ?>

			<?php } else { ?>
			<tr>
				<td class="center" colspan="12">
					<div><?php echo JText::_('COM_ES_WORKFLOWS_NO_WORKFLOWS_FOUND'); ?></div>
				</td>
			</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="12">
						<div class="footer-pagination">
							<?php echo $pagination->getListFooter(); ?>
						</div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<?php echo $this->html('form.hidden', 'customRedirect', 'index.php?option=com_easysocial&view=' . $view . '&layout=workflows'); ?>
	<?php echo $this->html('form.action', $view, 'fields', $view); ?>

	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
</form>
