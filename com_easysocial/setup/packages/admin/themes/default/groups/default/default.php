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
<form action="index.php?option=com_easysocial&view=groups" method="post" name="adminForm" class="esForm" id="adminForm" data-table-grid>
	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
		</div>

		<?php if($this->tmpl != 'component'){ ?>
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.published' , 'state' , $state); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<select class="o-form-control" name="type" id="filterType" data-table-grid-filter>
					<option value="all"<?php echo $type == 'all' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_FILTER_GROUP_TYPE'); ?></option>
					<option value="1"<?php echo $type === 1 ? ' selected="selected"' : '';?>><?php echo JText::_('COM_ES_CLUSTER_TYPE_PUBLIC'); ?></option>
					<option value="2"<?php echo $type === 2 ? ' selected="selected"' : '';?>><?php echo JText::_('COM_ES_CLUSTER_TYPE_PRIVATE'); ?></option>
					<option value="3"<?php echo $type === 3 ? ' selected="selected"' : '';?>><?php echo JText::_('COM_ES_CLUSTER_TYPE_INVITE_ONLY'); ?></option>
				</select>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.clusterCategories', 'category' , $category, 'group'); ?>
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

	<div id="profilesTable" class="panel-table" data-profiles>
		<table class="app-table table">
			<thead>
				<tr>
					<?php if($this->tmpl != 'component'){ ?>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					<?php } ?>

					<th style="text-align: left;">
						<?php echo $this->html('grid.sort', 'a.title', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'), $ordering, $direction); ?>
					</th>

					<?php if($this->tmpl != 'component'){ ?>
					<th class="center" width="5%">
						<?php echo $this->html('grid.sort', 'a.featured', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_FEATURED'), $ordering, $direction); ?>
					</th>
					<th class="center" width="5%">
						<?php echo $this->html('grid.sort', 'a.state', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATUS'), $ordering, $direction); ?>
					</th>

					<th class="center" width="5%">
						<?php echo $this->html('grid.sort', 'a.verified', 'COM_ES_TABLE_COLUMN_VERIFIED', $ordering , $direction ); ?>
					</th>

					<th class="center" width="15%">
						<?php echo $this->html('grid.sort', 'b.title', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CATEGORY'), $ordering, $direction); ?>
					</th>

					<th class="center" width="5%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TYPE');?>
					</th>

					<th class="center" width="15%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED_BY'); ?>
					</th>
					<?php } ?>

					<th width="2%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_USERS'); ?>
					</th>

					<?php if($this->tmpl != 'component') { ?>
					<th width="10%" class="center">
						<?php echo $this->html('grid.sort', 'a.created', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'), $ordering, $direction); ?>
					</th>
					<?php } ?>

					<th width="<?php echo $callback ? '10%' : '5%';?>" class="center">
						<?php echo $this->html('grid.sort', 'a.id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering, $direction); ?>
					</th>
				</tr>
			</thead>
			<tbody>

				<?php if($groups){ ?>
					<?php $i = 0; ?>
					<?php foreach($groups as $group){ ?>
					<tr class="row<?php echo $i; ?>"
						data-profiles-item
						data-grid-row
						data-title="<?php echo $this->html('string.escape', $group->getName());?>"
						data-id="<?php echo $group->id;?>"
					>
						<?php if($this->tmpl != 'component'){ ?>
						<td align="center">
							<?php echo $this->html('grid.id', $i, $group->id); ?>
						</td>
						<?php } ?>

						<td>
							<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=groups&layout=form&id=' . $group->id);?>"
								data-group-insert
								data-id="<?php echo $group->id;?>"
								data-alias="<?php echo $group->alias;?>"
								data-avatar="<?php echo $group->getAvatar();?>"
								data-title="<?php echo $this->html('string.escape', $group->getName());?>"
							>
								<?php echo $group->getName(); ?>
							</a>
						</td>

						<?php if ($this->tmpl != 'component') { ?>
						<td class="center">
							<?php echo $this->html('grid.featured', $group, 'groups', 'featured'); ?>
						</td>
						<td class="center">
							<?php echo $this->html('grid.published', $group, 'groups'); ?>
						</td>
						<td class="center">
							<?php echo $this->html('grid.published', $group, 'verifications', 'verified', ['enable', 'disable']); ?>
						</td>
						<td class="center">
							<a href="index.php?option=com_easysocial&view=groups&layout=categoryForm&id=<?php echo $group->getCategory()->id;?>"><?php echo $group->getCategory()->get('title'); ?></a>
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
							<a href="<?php echo ESR::url(array('view' => 'users', 'layout' => 'form', 'id' => $group->getCreator()->id)); ?>" target="_blank"><?php echo $group->getCreator()->getName();?></a>
						</td>
						<?php } ?>

						<td class="center">
							<?php echo $group->getTotalMembers(); ?>
						</td>

						<?php if ($this->tmpl != 'component') { ?>
						<td class="center">
							<?php echo ES::date($group->created)->format(JText::_('DATE_FORMAT_LC4')); ?>
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
							<?php echo JText::_('COM_EASYSOCIAL_GROUPS_NO_GROUPS_AVAILABLE');?>
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

	<?php echo JHTML::_('form.token'); ?>
	<?php if ($this->tmpl == 'component') { ?>
	<input type="hidden" name="tmpl" value="component" />
	<?php } ?>
	<input type="hidden" name="jscallback" value="<?php echo $this->html('string.escape', $this->input->get('jscallback', '', 'word'));?>" />
	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />

	<?php echo $this->html('form.action', 'groups', '', 'groups'); ?>

	<?php
	// Used by verifications
	echo $this->html('form.hidden', 'verification_type', SOCIAL_TYPE_GROUP);
	?>
</form>

<?php if ($this->tmpl != 'component') { ?>
<div id="toolbar-actions" class="btn-wrapper t-hidden" data-toolbar-actions="others">
	<div class="dropdown">
		<button type="button" class="btn btn-small dropdown-toggle" data-toggle="dropdown">
			<span class="icon-cog"></span> <?php echo JText::_('Other Actions');?> &nbsp;<span class="caret"></span>
		</button>

		<ul class="dropdown-menu">
			<li>
				<a href="javascript:void(0);" data-action="setVerified">
					<?php echo JText::_('COM_ES_SET_VERIFIED'); ?>
				</a>
			</li>
			<li>
				<a href="javascript:void(0);" data-action="removeVerified">
					<?php echo JText::_('COM_ES_REMOVE_VERIFIED'); ?>
				</a>
			</li>
			<li class="divider">
			<li>
				<a href="javascript:void(0);" data-action="switchOwner">
					<?php echo JText::_('COM_EASYSOCIAL_CHANGE_OWNER'); ?>
				</a>
			</li>
			<li class="divider">
			<li>
				<a href="javascript:void(0);" data-action="switchCategory">
					<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SWITCH_CATEGORY'); ?>
				</a>
			</li>
		</ul>
	</div>
</div>
<?php } ?>
