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
<form action="index.php" id="adminForm" method="post" name="adminForm" data-table-grid>
	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
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

					<th width="5%" class="text-center">
						<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering, $direction); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($articles) { ?>
				<?php $i = 0; ?>

				<?php foreach ($articles as $article) { ?>
				<tr class="row<?php echo $i; ?>" data-grid-row data-id="<?php echo $article->id;?>">
					<?php if (!$simple) { ?>
					<td>
						<?php echo $this->html('grid.id', $i++, $article->id); ?>
					</td>
					<?php } ?>


					<td>
						<a href="javascript:void(0);"
									data-article-insert
									data-id="<?php echo $article->id;?>"
									data-alias="<?php echo $article->alias;?>"
									data-title="<?php echo $this->html('string.escape', $article->title);?>"
						><?php echo $article->title;?></a>
					</td>

					<td class="text-center">
						<?php echo $article->id;?>
					</td>
				</tr>
				<?php } ?>

			<?php } else { ?>
				<tr class="is-empty">
					<td colspan="3" class="empty">
						<div>
							<?php echo JText::_('COM_EASYSOCIAL_CATEGORIES_EMPTY'); ?>
						</div>
					</td>
				</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="3">
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
	<input type="hidden" name="view" value="articles" />
	<input type="hidden" name="controller" value="articles" />

	<?php if ($jscallback) { ?>
	<input type="hidden" name="jscallback" value="<?php echo $jscallback;?>" />
	<?php } ?>

	<?php if ($simple) { ?>
	<input type="hidden" name="tmpl" value="component" />
	<?php } ?>
</form>
