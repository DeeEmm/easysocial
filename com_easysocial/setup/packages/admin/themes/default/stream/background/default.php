<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
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
					<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'); ?>
				</th>

				<th width="30%" class="center">
					<?php echo JText::_('COM_ES_COLOUR'); ?>
				</th>

				<th width="10%" class="center">
					<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATUS'); ?>
				</th>

				<th width="20%" class="center">
					<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'); ?>
				</th>

				<th width="5%" class="center">
					<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'); ?>
				</th>
			</thead>

			<tbody>
				<?php if ($items) { ?>
					<?php $i = 0; ?>

					<?php foreach ($items as $item) { ?>
					<tr>
						<td class="center">
							<?php echo $this->html('grid.id', $i, $item->id); ?>
						</td>

						<td>
							<a href="index.php?option=com_easysocial&view=stream&layout=backgroundForm&id=<?php echo $item->id;?>"><?php echo $item->title;?></a>
						</td>

						<td class="center">
							<span style="width: 100px;display: inline-block;border: 1px dashed #ccc; padding:0 10px; color: <?php echo $item->params->get('text_color');?>;
								<?php if ($item->params->get('type') == 'solid') { ?>
								background: <?php echo $item->params->get('first_color');?>;
								<?php } else { ?>
								background: linear-gradient(to bottom, <?php echo $item->params->get('first_color');?> 0%, <?php echo $item->params->get('second_color');?> 100%);
								<?php } ?>
							">Text</span>
						</td>

						<td class="center">
							<?php echo $this->html('grid.published', $item, 'background'); ?>
						</td>

						<td class="center">
							<?php echo $item->created;?>
						</td>

						<td class="center">
							<?php echo $item->id;?>
						</td>
					</tr>
					<?php } ?>
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

	<?php echo $this->html('form.action', 'background'); ?>
</form>
