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
<form action="index.php" method="post" name="adminForm" class="esForm" id="adminForm" data-table-grid>

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

	<div id="profilesTable" class="panel-table" data-profiles>
		<table class="app-table table">
			<thead>
				<tr>
					<?php if (!$callback) { ?>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					<?php } ?>

					<th style="text-align: left;">
						<?php echo $this->html('grid.sort' , 'title' , JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE') , $ordering , $direction); ?>
					</th>

					<?php if (!$callback) { ?>
					<th width="20%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ACTIONS'); ?>
					</th>
					<?php } ?>

					<th width="5%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_DRAFT'); ?>
					</th>

					<th width="10%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CATEGORY'); ?>
					</th>

					<th class="center" width="10%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TYPE');?>
					</th>

					<th width="10%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED_BY'); ?>
					</th>

					<?php if (!$callback) { ?>
					<th width="15%" class="center">
						<?php echo $this->html('grid.sort' , 'created' , JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED') , $ordering , $direction); ?>
					</th>
					<?php } ?>

					<th width="<?php echo $callback ? '10%' : '5%';?>" class="center">
						<?php echo $this->html('grid.sort' , 'id' , JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID') , $ordering , $direction); ?>
					</th>
				</tr>
			</thead>
			<tbody>

				<?php if ($pages) { ?>
					<?php $i = 0; ?>
					<?php foreach($pages as $page){ ?>
					<tr class="row<?php echo $i; ?>"
						data-profiles-item
						data-grid-row
						data-title="<?php echo $this->html('string.escape' , $page->getName());?>"
						data-id="<?php echo $page->id;?>"
					>
						<?php if (!$callback) { ?>
						<td align="center" valign="top">
							<?php echo $this->html('grid.id' , $i , $page->id); ?>
						</td>
						<?php } ?>

						<td>
							<a href="<?php echo ESR::_('index.php?option=com_easysocial&view=pages&layout=form&id=' . $page->id);?>" data-page-insert data-id="<?php echo $page->id;?>"><?php echo $page->getName(); ?></a>
						</td>

						<?php if (!$callback) { ?>
						<td class="center">
							<a href="javascript:void(0);" class="btn btn-sm btn-es-primary-o" data-pending-approve>
								<?php echo JText::_('COM_EASYSOCIAL_USER_APPROVE_BUTTON'); ?>
							</a>

							<a href="javascript:void(0);" class="btn btn-sm btn-es-danger-o" data-pending-reject>
								<?php echo JText::_('COM_EASYSOCIAL_USER_REJECT_BUTTON'); ?>
							</a>
						</td>

						<td class="center">
							<?php if ($page->isDraft()) { ?>
							<i class="fa fa-info-circle t-text--info" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_CLUSTERS_ITEM_IN_DRAFT_STATE'); ?>" data-es-provide="tooltip" style="font-size: 14px;"></i>
							<?php } else { ?>
							&mdash;
							<?php } ?>
						</td>

						<td class="center">
							<a href="index.php?option=com_easysocial&view=pages&layout=categoryForm&id=<?php echo $page->getCategory()->id;?>"><?php echo $page->getCategory()->get('title'); ?></a>
						</td>

						<td class="center">
							<?php if ($page->isOpen()) { ?>
								<?php echo JText::_('COM_ES_CLUSTER_TYPE_PUBLIC'); ?>
							<?php } ?>

							<?php if ($page->isClosed()) { ?>
								<?php echo JText::_('COM_ES_CLUSTER_TYPE_PRIVATE'); ?>
							<?php } ?>

							<?php if ($page->isInviteOnly()) { ?>
								<?php echo JText::_('COM_ES_CLUSTER_TYPE_INVITE_ONLY'); ?>
							<?php } ?>
						</td>

						<td class="center">
							<a href="<?php echo ESR::url(array('view' => 'users', 'layout' => 'form', 'id' => $page->getCreator()->id)); ?>" target="_blank"><?php echo $page->getCreator()->getName();?></a>
						</td>

						<td class="center">
							<?php echo ES::date($page->created)->format(JText::_('DATE_FORMAT_LC1'));?>
						</td>
						<?php } ?>

						<td class="center">
							<?php echo $page->id;?>
						</td>
					</tr>
						<?php $i++; ?>
					<?php } ?>
				<?php } else { ?>
					<tr class="is-empty">
						<td colspan="10" class="center">
							<?php echo JText::_('COM_EASYSOCIAL_PAGES_NO_PENDING_PAGES_CURRENTLY');?>
						</td>
					</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="10" class="center">
						<div class="footer-pagination"><?php echo $pagination->getListFooter(); ?></div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<?php echo $this->html('form.action', 'pages', '', 'pages'); ?>
	<input type="hidden" name="layout" value="pending" />
	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
</form>
