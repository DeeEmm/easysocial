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
<form name="adminForm" id="adminForm" method="post" data-table-grid>

	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<div class="o-form-inline">
				<div class="o-form-group t-lg-pl--xl">
					<?php echo $this->html('filter.lists', $versions, 'version', $version, JText::_('COM_EASYSOCIAL_FILTER_SELECT_VERSION'), 'all'); ?>
				</div>
			</div>
		</div>

		<div class="app-filter-bar__cell t-text--right t-lg-pr--xl">
			<div class="o-form-inline"><?php echo $this->html('filter.limit', $limit); ?></div>
		</div>
	</div>

	<div class="panel-table">
		<table class="app-table table">
			<thead>
				<th width="1%" class="center">
					<?php echo $this->html('grid.checkAll'); ?>
				</th>

				<th>
					<?php echo $this->html('grid.sort', 'title', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'), $ordering, $direction); ?>
				</th>
				<th width="10%" class="center">
					<?php echo $this->html('grid.sort', 'version', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_VERSION'), $ordering, $direction); ?>
				</th>
			</thead>

			<tbody>
			<?php if (!empty($scripts)) { ?>
				<?php $i = 0; ?>
				<?php foreach ($scripts as $script) { ?>
					<tr>
						<td><?php echo $this->html('grid.id', $i, $script->key); ?></td>
						<td>
							<div><b><?php echo $script->title; ?></b></div>
							<div class="t-fs--sm"><?php echo $script->description; ?></div>
						</td>
						<td class="center"><?php echo $script->version; ?></td>
					</tr>
					<?php $i++; ?>
				<?php } ?>
			<?php } else { ?>
				<tr class="is-empty">
					<td colspan="3" class="empty center">
						<div>
							<?php echo JText::_('COM_EASYSOCIAL_MAINTENANCE_LIST_EMPTY'); ?>
						</div>
					</td>
				</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="3">
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
	<input type="hidden" name="view" value="maintenance" />
	<input type="hidden" name="controller" value="maintenance" />
</form>
