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
<form method="post" name="adminForm" class="esForm" id="adminForm" data-table-grid>

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
					<?php if (!$simple) { ?>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					<?php } ?>

					<th>
						<?php echo $this->html('grid.sort', 'title', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'), $order, $direction); ?>
					</th>

					<?php if (!$simple) { ?>
					<th class="center" width="10%">
						<?php echo $this->html('grid.sort', 'state', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATUS'), $order, $direction); ?>
					</th>

					<th class="center" width="10%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_EVENTS');?>
					</th>
					<?php } ?>

					<?php if (!$simple) { ?>
					<th class="center" width="15%">
						<?php echo $this->html('grid.sort', 'lft', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ORDERING'), $order, $direction); ?>
						<?php echo $this->html('grid.order' , $categories); ?>
					</th>
					<?php } ?>

					<th width="<?php echo $simple ? '25%' : '15%';?>" class="center">
						<?php echo $this->html('grid.sort', 'created', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'), $order, $direction); ?>
					</th>

					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $order, $direction); ?>
					</th>
				</tr>
			</thead>
			<tbody>

				<?php if ($categories){ ?>
					<?php $i = 0; ?>
					<?php foreach ($categories as $category) { ?>
					<tr class="row<?php echo $i; ?>" data-grid-row data-id="<?php echo $category->id;?>">
						<?php if (!$simple) { ?>
						<td align="center">
							<?php echo $this->html('grid.id', $i, $category->id); ?>
						</td>
						<?php } ?>

						<td>
							<?php echo str_repeat('|&mdash;', $category->getDepth()); ?>
							<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=events&layout=categoryForm&id=' . $category->id);?>"
									data-category-insert
									data-id="<?php echo $category->id;?>"
									data-avatar="<?php echo $category->getAvatar();?>"
									data-alias="<?php echo $category->alias;?>"
									data-title="<?php echo $this->html('string.escape', $category->get('title'));?>"
								><?php echo $category->get('title'); ?></a>
						</td>

						<?php if (!$simple) { ?>
						<td class="center">
							<?php echo $this->html('grid.published', $category, 'events', '', array('publishCategory', 'unpublishCategory')); ?>
						</td>

						<td class="center">
							<?php echo $category->getTotalNodes(); ?>
						</td>
						<?php } ?>

						<?php if (!$simple) { ?>
						<td class="order center">
							<?php $orderkey = array_search($category->id, $ordering[$category->parent_id]); ?>
							<?php echo $this->html('grid.roworder', 'order', $orderkey + 1, count($ordering[$category->parent_id]), $saveOrder); ?>
							<?php $originalOrders[] = $orderkey + 1; ?>
						</td>
						<?php } ?>

						<td class="center">
							<?php echo $category->created; ?>
						</td>

						<td class="center">
							<?php echo $category->id;?>
						</td>
					</tr>
						<?php $i++; ?>
					<?php } ?>
				<?php } else { ?>
					<tr class="is-empty">
						<td colspan="<?php echo $simple ? '3' : '7'; ?>" class="center empty">
							<?php echo JText::_('COM_EASYSOCIAL_EVENT_CATEGORIES_NO_CATEGORIES_FOUND');?>
						</td>
					</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="<?php echo $simple ? '3' : '7'; ?>" class="center">
						<div class="footer-pagination"><?php echo $pagination->getListFooter(); ?></div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="ordering" value="<?php echo $order;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
	<input type="hidden" name="boxchecked" value="0" data-table-grid-box-checked />
	<input type="hidden" name="task" value="" data-table-grid-task />
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="view" value="events" />
	<input type="hidden" name="layout" value="categories" />
	<input type="hidden" name="controller" value="events" />
</form>
