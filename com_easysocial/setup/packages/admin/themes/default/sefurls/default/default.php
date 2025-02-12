<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<form name="adminForm" id="adminForm" method="post" action="index.php" data-table-grid>

	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search', $search); ?>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<select class="o-form-control" name="type" id="filterType" data-table-grid-filter>
					<option value=""<?php echo $type == '' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_ES_SEFURLS_FILTER_SELECT'); ?></option>
					<option value="all"<?php echo $type == 'all' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_ES_SEFURLS_FILTER_ALL'); ?></option>
					<option value="custom"<?php echo $type == 'custom' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_ES_SEFURLS_FILTER_CUSTOM'); ?></option>
				</select>
			</div>
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
						<?php echo $this->html('grid.sort', 'sefurl', JText::_('SEF url'), $ordering, $direction); ?>
					</th>

					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering, $direction); ?>
					</th>
				</tr>
			</thead>

			<tbody>
			<?php if ($urls) { ?>
				<?php $i = 0; ?>
				<?php foreach ($urls as $url) { ?>
				<tr data-workflow-item data-title="<?php echo $this->html('string.escape', $url->sefurl);?>" data-id="<?php echo $url->id;?>">
					<td>
						<?php echo $this->html('grid.id', $i, $url->id); ?>
					</td>

					<td style="text-align:left;">
						<a href="<?php echo ESR::_('index.php?option=com_easysocial&view=sefurls&layout=form&id=' . $url->id); ?>">
							<?php echo $url->sefurl; ?>
						</a>

						<?php if ($url->custom) { ?>
							<i class="fa fa-wrench"></i>
						<?php } ?>

						<div class="t-fs--sm">
							<?php echo JText::sprintf('COM_ES_SEFURLS_NON_SEF_URL', $url->rawurl); ?>
						</div>
					</td>

					<td class="center">
						<?php echo $url->id;?>
					</td>
				</tr>
				<?php $i++; ?>
				<?php } ?>

			<?php } else { ?>
			<tr>
				<td class="center" colspan="12">
					<div><?php echo JText::_('COM_ES_SEFURLS_NO_URLS'); ?></div>
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

	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
	<input type="hidden" name="boxchecked" value="0" data-table-grid-box-checked />
	<input type="hidden" name="task" value="" data-table-grid-task />
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="view" value="sefurls" />
	<input type="hidden" name="controller" value="sefurls" />
</form>
