<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
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
			<?php echo $this->html('filter.search', $search); ?>
		</div>

		<?php if ($this->tmpl != 'component') { ?>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.published', 'state', $state); ?>
			</div>
		</div>
		<?php } ?>

		<div class="app-filter-bar__cell app-filter-bar__cell--empty"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit', $limit); ?>
			</div>
		</div>
	</div>

	<div class="panel-table">
		<table class="app-table table">
			<thead>
				<?php if ($this->tmpl != 'component') { ?>
				<th width="1%" class="center">
					<?php echo $this->html('grid.checkAll'); ?>
				</th>
				<?php } ?>

				<th>
					<?php echo $this->html('grid.sort', 'title', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'), $ordering, $direction); ?>
				</th>

				<?php if($this->tmpl != 'component'){ ?>
				<th width="1%" class="center">
					<?php echo $this->html('grid.sort', 'state', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATE'), $ordering, $direction); ?>
				</th>

				<th width="5%" class="center">
					<?php echo $this->html('grid.sort', 'type', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TYPE'), $ordering, $direction); ?>
				</th>

				<th width="10%" class="center">
					<?php echo $this->html('grid.sort', 'created', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'), $ordering, $direction); ?>
				</th>
				<?php } ?>

				<th width="<?php echo $this->tmpl == 'component' ? '8%' : '5%';?>" class="center">
					<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering, $direction); ?>
				</th>
			</thead>

			<tbody>
			<?php if ($emoticons) { ?>
				<?php $i = 0; ?>
				<?php foreach($emoticons as $emoticon){ ?>
				<tr>

					<?php if($this->tmpl != 'component'){ ?>
					<td class="center">
						<?php echo $this->html('grid.id', $i, $emoticon->id); ?>
					</td>
					<?php } ?>

					<td>
						<?php echo $emoticon->getIcon(); ?>
						&nbsp;
						<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=emoticons&layout=form&id=' . $emoticon->id);?>">
							<?php echo $emoticon->get('title'); ?>
						</a>
					</td>

					<?php if ($this->tmpl != 'component') { ?>
					<td class="center">
						<?php echo $this->html('grid.published', $emoticon, 'emoticons'); ?>
					</td>

					<td class="center">
						<?php echo $emoticon->type;?>
					</td>

					<td class="center">
						<?php echo $emoticon->created;?>
					</td>
					<?php } ?>

					<td class="center">
						<?php echo $emoticon->id;?>
					</td>
				</tr>
				<?php } ?>
			<?php } else { ?>
				<tr class="is-empty">
					<td class="empty" colspan="8">
						<?php echo JText::_('COM_ES_EMOTICONS_LIST_EMPTY'); ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="8">
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
	<input type="hidden" name="view" value="emoticons" />
	<input type="hidden" name="controller" value="emoticons" />

</form>
