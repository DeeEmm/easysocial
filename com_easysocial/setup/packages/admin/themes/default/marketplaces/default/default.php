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
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
		</div>
		<?php if($this->tmpl != 'component'){ ?>
			<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
				<div class="app-filter-bar__filter-wrap">
					<?php echo $this->html('filter.published', 'state', $state); ?>
				</div>
			</div>

			<div class="app-filter-bar__cell app-filter-bar__cell--divider-left"></div>

			<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
				<div class="app-filter-bar__filter-wrap">
					<?php echo $this->html('filter.limit' , $limit); ?>
				</div>
			</div>
		<?php } ?>
	</div>

	<div id="marketplaces" class="panel-table">
		<table class="app-table table">
			<thead>
				<tr>
					<?php if (!$simple) { ?>
					<th width="5">
						<input type="checkbox" name="toggle" value="" data-table-grid-checkall />
					</th>
					<?php } ?>

					<th style="text-align: left;">
						<?php echo $this->html('grid.sort', 'title', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'), $ordering, $direction); ?>
					</th>

					<?php if (!$simple) { ?>
					<th width="5%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_FEATURED'); ?>
					</th>

					<th width="5%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATE'); ?>
					</th>

					<th width="10%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CATEGORY'); ?>
					</th>
					<?php } ?>

					<th class="center" width="10%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED_BY'); ?>
					</th>

					<th width="10%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'); ?>
					</th>
					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering, $direction); ?>
					</th>
				</tr>
			</thead>

			<tbody>
			<?php if ($items) { ?>
				<?php $i = 0; ?>

				<?php foreach ($items as $item) { ?>
				<tr>
					<?php if (!$simple) { ?>
					<td>
						<?php echo $this->html('grid.id', $i++, $item->id); ?>
					</td>
					<?php } ?>

					<td align="left">
						<a href="<?php echo ($simple) ? 'javascript:void(0);' : FRoute::_('index.php?option=com_easysocial&view=marketplaces&layout=form&id=' . $item->id);?>"
							data-item-insert
							data-id="<?php echo $item->id;?>"
							data-alias="<?php echo $item->getAlias();?>"
							data-title="<?php echo $this->html('string.escape', $item->title);?>"
						><?php echo $this->html('string.escape', $item->title);?></a>
					</td>

					<?php if (!$simple) { ?>
					<td class="center">
						<?php echo $this->html('grid.featured', $item, 'marketplaces', 'featured'); ?>
					</td>
					<td class="center">
						<?php echo $this->html('grid.published', $item, 'marketplaces'); ?>
					</td>
					<td style="text-align: center;">
						<a href="<?php echo ESR::url(array('view' => 'marketplaces', 'layout' => 'categoryForm', 'id' => $item->category_id)); ?>"><?php echo $item->getCategory()->title;?></a>
					</td>

					<?php } ?>

					<td class="center">
						<a href="<?php echo ESR::url(array('view' => 'users', 'layout' => 'form', 'id' => $item->getCreator()->id)); ?>" target="_blank"><?php echo $item->getCreator()->getName(); ?></a>
					</td>

					<td class="center">
						<?php echo ES::date($item->created)->format(JText::_('DATE_FORMAT_LC4')); ?>
					</td>
					<td class="center">
						<?php echo $item->id;?>
					</td>
				</tr>
				<?php } ?>

			<?php } else { ?>
				<tr class="is-empty">
					<td colspan="7" class="center empty">
						<div>
							<?php echo JText::_('COM_ES_MARKETPLACES_EMPTY_MESSAGE'); ?>
						</div>
					</td>
				</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="7">
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
	<input type="hidden" name="controller" value="marketplaces" />
	<input type="hidden" name="view" value="marketplaces" />
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
		</ul>
	</div>
</div>
<?php } ?>
