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
<form action="index.php?option=com_easysocial&view=apps<?php echo $group ? '&group=' . $group : '';?>" method="post" name="adminForm" class="esForm" id="adminForm" data-table-grid>

	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.published', 'state', $state, array(array('value' => 'outdated', 'title' => 'COM_ES_OUTDATED'))); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<select name="group" class="o-form-control" data-table-grid-filter>
					<option value="all"<?php echo $group == 'all' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_EASYSOCIAL_FILTER_SELECT_GROUP');?></option>
					<option value="group"<?php echo $group == 'group' ? ' selected="selected"' : '';?>><?php echo JText::_( 'COM_EASYSOCIAL_GRID_FILTER_TYPE_GROUPS' );?></option>
					<option value="user"<?php echo $group == 'user' ? ' selected="selected"' : '';?>><?php echo JText::_( 'COM_EASYSOCIAL_GRID_FILTER_TYPE_USERS' );?></option>
					<option value="event"<?php echo $group == 'event' ? ' selected="selected"' : '';?>><?php echo JText::_( 'COM_EASYSOCIAL_GRID_FILTER_TYPE_EVENT' );?></option>
					<option value="page"<?php echo $group == 'page' ? ' selected="selected"' : '';?>><?php echo JText::_( 'COM_EASYSOCIAL_GRID_FILTER_TYPE_PAGE' );?></option>
					<option value="marketplace"<?php echo $group == 'marketplace' ? ' selected="selected"' : '';?>><?php echo JText::_( 'COM_EASYSOCIAL_GRID_FILTER_TYPE_MARKETPLACE' );?></option>
				</select>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--empty"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit' , $limit); ?>
			</div>
		</div>
	</div>

	<?php if ($outdatedApps && $filter != 'outdated') { ?>
	<div class="t-mt--lg t-mb--lg">
		<div class="o-alert o-alert--warning">
			<i class="fa fa-refresh"></i>&nbsp; <?php echo JText::sprintf('COM_EASYSOCIAL_APPS_THERE_ARE_APPS_REQUIRING_UPDATES', count($outdatedApps)); ?>
			<a href="<?php echo JRoute::_('index.php?option=com_easysocial&view=apps&filter=outdated');?>" class="btn btn-es-default-o btn-sm t-lg-ml--xl"><?php echo jText::_('COM_EASYSOCIAL_VIEW_APPS_BUTTON');?></a>
		</div>
	</div>
	<?php } ?>

	<div id="appsTable" class="panel-table">
		<table class="app-table table">
			<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>

					<th style="text-align: left;">
						<?php echo $this->html('grid.sort', 'title', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE' ) , $ordering , $direction ); ?>
					</th>

					<th class="center" width="5%">
						&nbsp;
					</th>

					<?php if ($filter != 'outdated') { ?>
						<th class="center" width="5%">
							<?php echo $this->html('grid.sort', 'state', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATUS'), $ordering, $direction); ?>
						</th>

						<?php if ($layout != 'fields') { ?>
						<th class="center" width="5%">
							<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_DEFAULT'); ?>
						</th>
						<?php } ?>
					<?php } ?>

					<?php if ($filter == 'outdated') { ?>
					<th class="center" width="10%">
						&nbsp;
					</th>
					<?php } ?>

					<th class="center" width="10%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_VERSION'); ?>
					</th>

					<?php if ($filter == 'outdated') { ?>
					<th class="center" width="10%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_LATEST_VERSION'); ?>
					</th>
					<?php } ?>

					<?php if ($filter != 'outdated') { ?>
					<th class="center" width="10%">
						<?php echo $this->html('grid.sort', 'group' , JText::_('COM_EASYSOCIAL_TABLE_COLUMN_GROUP') , $ordering , $direction ); ?>
					</th>

					<th width="10%" class="t-text--center">
						<?php echo $this->html('grid.sort', 'created' , JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ELEMENT'), $ordering , $direction ); ?>
					</th>
					<?php } ?>

					<?php if ($group != 'all') { ?>
					<th class="center" width="10%">
						<?php echo $this->html('grid.sort', 'ordering', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ORDERING'), $ordering, $direction); ?>
						<?php echo $this->html('grid.order' , $apps); ?>
					</th>
					<?php } ?>

					<th width="5%" class="t-text--center">
						<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID') , $ordering , $direction ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if( $apps ){ ?>
					<?php $i = 0; ?>
					<?php foreach( $apps as $app ){ ?>
					<tr class="row<?php echo $i; ?>" data-grid-row data-id="<?php echo $app->id;?>">
						<td class="center">
							<?php echo $this->html('grid.id', $i, $app->id); ?>
						</td>
						<td>
							<a href="index.php?option=com_easysocial&view=apps&layout=form&id=<?php echo $app->id;?>"><?php echo $app->get('title'); ?></a>
						</td>

						<td class="center">
							<?php if ($filter != 'outdated' && $app->isAvailableInStore() && $app->isOutdated() && $app->getAppStoreItem()->isDownloadable() && $app->getAppStoreItem()->isDownloadableFromApi()) { ?>
								<a href="javascript:void(0);" class="btn btn-es-primary-o btn-sm" data-app-update data-id="<?php echo $app->getAppStoreItem()->id;?>"><?php echo JText::_('Update'); ?></a>
							<?php } ?>
						</td>

						<?php if ($filter != 'outdated') { ?>
							<td class="center">
								<?php echo $this->html('grid.published', $app, 'apps', '', array(), array(), array(), $app->system ? false : true); ?>
							</td>

							<?php if ($layout != 'fields') { ?>
							<td class="center">
								<?php echo $this->html('grid.featured', $app, 'apps', 'default'); ?>
							</td>
							<?php } ?>
						<?php } ?>

						<?php if ($filter == 'outdated') { ?>
						<td class="center">
							<a href="javascript:void(0);" class="btn btn-es-default btn-sm t-lg-ml--xl" data-app-update data-id="<?php echo $app->getAppStoreItem()->id;?>"><?php echo JText::_('Update'); ?></a>
						</td>
						<?php } ?>

						<td class="center">
							<?php if ($app->isAvailableInStore()) { ?>
								<?php if ($app->isOutdated()) { ?>
									<span class="t-text--danger" data-es-provide="tooltip" data-original-title="<?php echo JText::sprintf('This app is outdated. The latest version available is %1$s', $app->getAppStoreItem()->version);?>">
										<b><?php echo $app->getVersion();?></b>
									</span>
								<?php } else { ?>
									<span class="t-text--success" data-es-provide="tooltip" data-original-title="<?php echo JText::_('Great! This app is up to date');?>">
										<b><?php echo $app->getVersion();?></b>
									</span>
								<?php } ?>
							<?php } else { ?>
								<?php echo $app->getVersion();?>
							<?php } ?>
						</td>

						<?php if ($filter == 'outdated') { ?>
						<td class="center">
							<span class="t-text--success"><b><?php echo $app->getAppStoreItem()->version;?></b></span>
						</td>
						<?php } ?>

						<?php if ($filter != 'outdated') { ?>
						<td class="center">
							<?php echo $app->group; ?>
						</td>

						<td class="center">
							<?php echo $app->element; ?>
						</td>
						<?php } ?>

						<?php if ($group != 'all') { ?>
						<td class="center">
							<?php echo $this->html('grid.ordering', count($apps), ($i + 1), $ordering == 'ordering',  $app->ordering); ?>
						</td>
						<?php } ?>

						<td class="center">
							<?php echo $app->id;?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>
				<?php } else { ?>
					<tr class="is-empty">
						<td colspan="9" class="center empty">
							<?php echo JText::_('COM_EASYSOCIAL_APPS_NO_APPS_FOUND');?>
						</td>
					</tr>
				<?php } ?>
			</tbody>

			<?php if ($filter != 'outdated') { ?>
			<tfoot>
				<tr>
					<td colspan="9" class="center">
						<div class="footer-pagination"><?php echo $pagination->getListFooter(); ?></div>
					</td>
				</tr>
			</tfoot>
			<?php } ?>
		</table>
	</div>

	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
	<input type="hidden" name="boxchecked" value="0" data-table-grid-box-checked />
	<input type="hidden" name="task" value="" data-table-grid-task />
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="view" value="apps" />

	<?php if ($layout == 'fields') { ?>
	<input type="hidden" name="layout" value="fields" />
	<?php } ?>

	<input type="hidden" name="controller" value="apps" data-table-grid-controllers />
</form>
