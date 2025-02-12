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

				<th width="15%" class="center">
					<?php echo JText::_('COM_ES_TABLE_COLUMN_USER'); ?>
				</th>


				<?php if ($this->tmpl != 'component') { ?>
				<th width="15%" class="center">
					<?php echo $this->html('grid.sort', 'priority', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_PRIORITY'), $ordering, $direction); ?>
				</th>

				<th width="15%" class="center">
					<?php echo $this->html('grid.sort', 'state', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATE'), $ordering, $direction); ?>
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
			<?php if ($ads) { ?>
				<?php $i = 0; ?>
				<?php foreach ($ads as $ad) { ?>
				<tr>

					<?php if($this->tmpl != 'component'){ ?>
					<td class="center">
						<?php echo $this->html('grid.id', $i, $ad->id); ?>
					</td>
					<?php } ?>

					<td>
						<a href="<?php echo ESR::_('index.php?option=com_easysocial&view=ads&layout=form&id=' . $ad->id);?>">
							<?php echo $ad->title; ?>
						</a>
					</td>

					<td class="center">
						<?php if (!$ad->getOwner()->id) { ?>
							&mdash;
						<?php } else { ?>
							<a href="index.php?option=com_easysocial&view=users&layout=form&id=<?php echo $ad->getOwner()->id;?>"><?php echo $ad->getOwner()->getName();?></a>
						<?php } ?>
					</td>

					<?php if ($this->tmpl != 'component') { ?>
					<td class="center">
						<?php echo $ad->getPriority();?>
					</td>

					<td class="center">
						<?php if ($ad->isUnderModeration()) { ?>
							<span data-es-provide="tooltip" data-original-title="<?php echo JText::_('Ad is currently under moderation. Use the Approve or Reject button to manage the ad');?>">
								<?php echo JText::_('Under Moderation'); ?>
							</span>
						<?php } ?>

						<?php if ($ad->isDraft()) { ?>
							<span data-es-provide="tooltip" data-original-title="<?php echo JText::_('Ad has been rejected. Once the user submits the ad for review again, it will be under moderation');?>">
								<?php echo JText::_('Rejected'); ?>
							</span>
						<?php } ?>
					</td>

					<td class="center">
						<?php echo $ad->created;?>
					</td>
					<?php } ?>

					<td class="center">
						<?php echo $ad->id;?>
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
	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
	<input type="hidden" name="boxchecked" value="0" data-table-grid-box-checked />
	<input type="hidden" name="task" value="" data-table-grid-task />
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="view" value="ads" />
	<input type="hidden" name="controller" value="ads" />

</form>
