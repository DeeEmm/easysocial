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
					<?php if( !$callback ){ ?>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					<?php } ?>

					<th style="text-align: left;">
						<?php echo $this->html( 'grid.sort' , 'title' , JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_TITLE' ) , $ordering , $direction ); ?>
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
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TYPE'); ?>
					</th>

					<th width="10%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CATEGORY'); ?>
					</th>

					<th width="10%" class="center">
						<?php echo JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_CREATED_BY' ); ?>
					</th>

					<?php if (!$callback) { ?>
					<th width="15%" class="center">
						<?php echo $this->html( 'grid.sort' , 'created' , JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_CREATED' ) , $ordering , $direction ); ?>
					</th>
					<?php } ?>

					<th width="<?php echo $callback ? '10%' : '5%';?>" class="center">
						<?php echo $this->html( 'grid.sort' , 'id' , JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_ID' ) , $ordering , $direction ); ?>
					</th>
				</tr>
			</thead>
			<tbody>

				<?php if( $groups ){ ?>
					<?php $i = 0; ?>
					<?php foreach( $groups as $group ){ ?>
					<tr class="row<?php echo $i; ?>" data-profiles-item data-grid-row data-title="<?php echo $this->html( 'string.escape' , $group->getName() );?>" data-id="<?php echo $group->id;?>">

						<?php if (!$callback) { ?>
						<td align="center" valign="top">
							<?php echo $this->html('grid.id' , $i , $group->id); ?>
						</td>
						<?php } ?>

						<td>
							<a href="<?php echo ESR::_('index.php?option=com_easysocial&view=groups&layout=form&id=' . $group->id );?>" data-group-insert data-id="<?php echo $group->id;?>"><?php echo $group->getName(); ?></a>
						</td>

						<?php if( !$callback ){ ?>
						<td class="center">
							<a href="javascript:void(0);" class="btn btn-sm btn-es-primary-o" data-pending-approve>
								<?php echo JText::_( 'COM_EASYSOCIAL_USER_APPROVE_BUTTON' ); ?>
							</a>

							<a href="javascript:void(0);" class="btn btn-sm btn-es-danger-o" data-pending-reject>
								<?php echo JText::_( 'COM_EASYSOCIAL_USER_REJECT_BUTTON' ); ?>
							</a>
						</td>

						<td class="center">
							<?php if ($group->isDraft()) { ?>
							<i class="fa fa-info-circle t-text--info" data-original-title="<?php echo JText::_('COM_EASYSOCIAL_CLUSTERS_ITEM_IN_DRAFT_STATE'); ?>" data-es-provide="tooltip" style="font-size: 14px;"></i>
							<?php } else { ?>
							&mdash;
							<?php } ?>
						</td>

						<td class="center">
							<?php if($group->isOpen()){ ?>
								<?php echo JText::_('COM_ES_CLUSTER_TYPE_PUBLIC'); ?>
							<?php } ?>

							<?php if($group->isClosed()){ ?>
								<?php echo JText::_('COM_ES_CLUSTER_TYPE_PRIVATE'); ?>
							<?php } ?>

							<?php if($group->isInviteOnly()){ ?>
								<?php echo JText::_('COM_ES_CLUSTER_TYPE_INVITE_ONLY'); ?>
							<?php } ?>
						</td>

						<td class="center">
							<a href="index.php?option=com_easysocial&view=groups&layout=categoryForm&id=<?php echo $group->getCategory()->id;?>"><?php echo $group->getCategory()->get('title'); ?></a>
						</td>

						<td class="center">
							<a href="<?php echo ESR::url(array('view' => 'users', 'layout' => 'form', 'id' => $group->getCreator()->id)); ?>" target="_blank"><?php echo $group->getCreator()->getName();?></a>
						</td>

						<td class="center">
							<?php echo ES::date( $group->created )->format( JText::_( 'DATE_FORMAT_LC1' ) );?>
						</td>
						<?php } ?>

						<td class="center">
							<?php echo $group->id;?>
						</td>
					</tr>
						<?php $i++; ?>
					<?php } ?>
				<?php } else { ?>
					<tr class="is-empty">
						<td colspan="10" class="center empty">
							<?php echo JText::_( 'COM_EASYSOCIAL_GROUPS_NO_PENDING_GROUPS_CURRENTLY' );?>
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

	<?php echo $this->html('form.action', 'groups', '', 'groups'); ?>
	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
	<input type="hidden" name="layout" value="pending" />
</form>
