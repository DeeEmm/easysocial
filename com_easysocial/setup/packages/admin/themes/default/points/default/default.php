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
<form name="adminForm" id="adminForm" method="post" data-table-grid>
	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
		</div>

		<?php if ($this->tmpl != 'component') { ?>
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.lists' , $extensions , 'extension' , $extension, 'COM_EASYSOCIAL_FILTER_SELECT_EXTENSION', 'all'); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.published', 'published', $state); ?>
			</div>
		</div>
		<?php } ?>

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
					<?php echo $this->html('grid.sort', 'title', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'), $ordering, $direction); ?>
				</th>

				<?php if ($this->tmpl != 'component' ){ ?>
				<th width="10%" class="center">
					<?php echo $this->html('grid.sort', 'state', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATUS'), $ordering, $direction); ?>
				</th>
				<?php } ?>

				<th width="10%" class="center">
					<?php echo $this->html('grid.sort', 'points', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_POINTS'), $ordering, $direction); ?>
				</th>

				<th width="15%" class="center">
					<?php echo $this->html('grid.sort', 'extension', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_EXTENSION'), $ordering, $direction); ?>
				</th>

				<?php if ($this->tmpl != 'component' ){ ?>
				<th width="15%" class="center">
					<?php echo $this->html('grid.sort', 'created', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'), $ordering, $direction); ?>
				</th>
				<?php } ?>

				<th width="<?php echo $this->tmpl == 'component' ? '10%' : '5%';?>" class="center">
					<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering , $direction); ?>
				</th>
			</thead>

			<tbody>
			<?php if ($points) { ?>
				<?php $i = 0; ?>
				<?php foreach ($points as $point) { ?>
				<tr>
					<td class="center">
						<?php echo $this->html('grid.id', $i, $point->id); ?>
					</td>

					<td>
						<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=points&layout=form&id=' . $point->id );?>"
							data-es-provide="tooltip"
							data-placement="bottom"
							data-points-insert
							data-id="<?php echo $point->id;?>"
							data-title="<?php echo $point->get('title');?>"
							data-original-title="<?php echo JText::_($point->description);?>"
							data-alias="<?php echo $point->getAlias();?>"
						><?php echo $point->get('title'); ?></a>
					</td>

					<?php if( $this->tmpl != 'component' ){ ?>
					<td class="center">
						<?php echo $this->html( 'grid.published' , $point , 'access' ); ?>
					</td>
					<?php } ?>

					<td class="center">
						<?php echo $point->points; ?>
					</td>

					<td class="center">
						<?php echo $point->getExtensionTitle(); ?>
					</td>

					<?php if( $this->tmpl != 'component' ){ ?>
					<td class="center">
						<?php echo $point->created;?>
					</td>
					<?php } ?>

					<td class="center">
						<?php echo $point->id;?>
					</td>
				</tr>
				<?php } ?>
			<?php } else { ?>
				<tr class="is-empty">
					<td colspan="<?php echo ($this->tmpl != 'component' ) ? 7 : 5; ?>" class="empty center">
						<div><?php echo JText::_( 'COM_EASYSOCIAL_POINTS_LIST_EMPTY' ); ?></div>
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

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
<input type="hidden" name="boxchecked" value="0" data-table-grid-box-checked />
<input type="hidden" name="task" value="" data-table-grid-task />
<input type="hidden" name="option" value="com_easysocial" />
<input type="hidden" name="view" value="points" />
<input type="hidden" name="controller" value="points" />
</form>
