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
			<?php echo $this->html('filter.search', $search); ?>
		</div>

		<?php if ($this->tmpl != 'component') { ?>
		<div class="app-filter-bar__cell app-filter-bar__cell--empty"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit', $limit); ?>
			</div>
		</div>
		<?php } ?>
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
					<?php echo $this->html('grid.sort', 'name', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_NAME'), $ordering, $direction); ?>
				</th>

				<th width="20%" class="center">
					<?php echo JText::_('COM_ES_TABLE_COLUMN_USER'); ?>
				</th>

				<th width="10%" class="center">
					<?php echo $this->html('grid.sort', 'created', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'), $ordering, $direction); ?>
				</th>

				<th width="<?php echo $callback ? '10%' : '5%';?>" class="center">
					<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering, $direction); ?>
				</th>
			</thead>

			<tbody>
			<?php if ($advertisers) { ?>
				<?php $i = 0; ?>
				<?php foreach ($advertisers as $advertiser) { ?>
				<tr>

					<?php if($this->tmpl != 'component'){ ?>
					<td class="center">
						<?php echo $this->html('grid.id', $i, $advertiser->id); ?>
					</td>
					<?php } ?>

					<td>
						<a href="<?php echo ESR::_('index.php?option=com_easysocial&view=ads&layout=advertiserForm&id=' . $advertiser->id);?>"
							data-advertiser-insert
							data-id="<?php echo $advertiser->id;?>"
							data-title="<?php echo $this->html('string.escape', $advertiser->getCompanyName());?>"
							>
							<?php echo $advertiser->getCompanyName(); ?>
						</a>
					</td>

					<td class="center">
						<?php if ($advertiser->user_id) { ?>
							<?php echo $advertiser->getUser()->getName();?>
						<?php } else { ?>
							N/A
						<?php } ?>
					</td>

					<td class="center">
						<?php echo $advertiser->created;?>
					</td>

					<td class="center">
						<?php echo $advertiser->id;?>
					</td>
				</tr>
				<?php } ?>
			<?php } else { ?>
				<tr class="is-empty">
					<td class="empty" colspan="8">
						<?php echo JText::_('COM_ES_ADS_LIST_EMPTY'); ?>
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
	<?php if ($this->tmpl == 'component') { ?>
	<input type="hidden" name="tmpl" value="component" />
	<?php } ?>
	<input type="hidden" name="jscallback" value="<?php echo $this->html('string.escape', $this->input->get('jscallback', '', 'word'));?>" />
	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
	<input type="hidden" name="boxchecked" value="0" data-table-grid-box-checked />
	<input type="hidden" name="task" value="" data-table-grid-task />
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="view" value="ads" />
	<input type="hidden" name="controller" value="ads" />

</form>
