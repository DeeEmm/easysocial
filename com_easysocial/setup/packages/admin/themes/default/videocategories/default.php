<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<form action="index.php" id="adminForm" method="post" name="adminForm" data-table-grid>
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

	<div id="pendingUsersTable" class="panel-table">
		<table class="app-table table" data-pending-users>
			<thead>
				<tr>
					<?php if (!$simple) { ?>
					<th width="5">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					<?php } ?>
					<th style="text-align: left;">
						<?php echo $this->html('grid.sort', 'title', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'), $ordering, $direction); ?>
					</th>

					<?php if (!$simple) { ?>
					<th width="5%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_DEFAULT'); ?>
					</th>
					<th width="5%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATE'); ?>
					</th>
					<?php } ?>

					<th width="5%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_VIDEOS'); ?>
					</th>

					<?php if (!$simple) { ?>
					<th class="center" width="10%">
						<?php echo $this->html('grid.sort', 'ordering', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ORDERING'), $ordering, $direction); ?>
						<?php echo $this->html('grid.order' , $categories); ?>
					</th>
					<?php } ?>

					<th width="5%" class="center">
						<?php echo $this->html( 'grid.sort' , 'id' , JText::_( 'COM_EASYSOCIAL_USERS_ID' ) , $ordering , $direction ); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($categories) { ?>
				<?php $i = 0; ?>

				<?php foreach ($categories as $category) { ?>
					<tr class="row<?php echo $i; ?>" data-grid-row data-id="<?php echo $category->id;?>">
						<?php if (!$simple) { ?>
							<td align="center">
								<?php echo $this->html('grid.id', $i, $category->id); ?>
							</td>
						<?php } ?>

						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_easysocial&view=videocategories&layout=form&id=' . $category->id);?>"
										data-category-insert
										data-id="<?php echo $category->id;?>"
										data-alias="<?php echo $category->getAlias();?>"
										data-title="<?php echo $this->html('string.escape', $category->get('title'));?>"
							><?php echo JText::_($category->title);?></a>
						</td>

						<?php if (!$simple) { ?>
						<td class="center">
							<?php echo $this->html('grid.featured', $category, 'videocategories'); ?>
						</td>
						<td class="center">
							<?php echo $this->html('grid.published', $category, 'videocategories'); ?>
						</td>
						<?php } ?>

						<td class="center">
							<?php echo $category->getTotalVideos();?>
						</td>

						<?php if (!$simple) { ?>
						<td class="order center">
							<?php echo $this->html('grid.ordering', count($categories), ($i + 1), $ordering == 'ordering',  $category->ordering); ?>
						</td>
						<?php } ?>

						<td class="center">
							<?php echo $category->id;?>
						</td>

					</tr>
					<?php $i++; ?>
				<?php } ?>

			<?php } else { ?>
				<tr class="is-empty">
					<td colspan="6" class="empty">
						<div>
							<?php echo JText::_('COM_EASYSOCIAL_CATEGORIES_EMPTY'); ?>
						</div>
					</td>
				</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="6">
						<div class="footer-pagination">
							<?php echo $pagination->getListFooter(); ?>
						</div>
					</td>
				</tr>
			</tfoot>

		</table>
	</div>

	<?php echo $this->html('form.action', 'videocategories'); ?>

	<?php if ($this->tmpl == 'component') { ?>
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="jscallback" value="<?php echo $this->input->get('jscallback', '', 'cmd');?>" />
	<?php } ?>

	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
	<input type="hidden" name="view" value="videocategories" />
</form>
