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
<form action="index.php?option=com_easysocial&view=<?php echo $view;?>&layout=fields" method="post" name="adminForm" class="esForm" id="adminForm" data-table-grid>

	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.published', 'state', $state); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--empty"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit' , $limit); ?>
			</div>
		</div>
	</div>

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

					<th class="center" width="10%">
						<?php echo $this->html('grid.sort', 'state', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATUS'), $ordering, $direction); ?>
					</th>

					<th class="center" width="20%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TYPE'); ?>
					</th>

					<th class="center" width="10%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_VERSION'); ?>
					</th>

					<th width="5%" class="t-text--center">
						<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID') , $ordering , $direction ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($apps) { ?>
					<?php $i = 0; ?>
					<?php foreach ($apps as $app) { ?>
					<tr>
						<td class="center">
							<?php echo $this->html('grid.id', $i++, $app->id); ?>
						</td>
						<td>
							<a href="index.php?option=com_easysocial&view=<?php echo $view;?>&layout=fieldsform&id=<?php echo $app->id;?>"><?php echo $app->get('title'); ?></a>
						</td>

						<td class="center">
							<?php echo $this->html('grid.published', $app, 'apps', '', array(), array(), array(), $app->system ? false : true); ?>
						</td>

						<td class="center">
							<?php echo $app->element; ?>
						</td>

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

						<td class="center">
							<?php echo $app->id;?>
						</td>
					</tr>
					<?php } ?>
				<?php } else { ?>
					<tr class="is-empty">
						<td colspan="8" class="center empty">
							<?php echo JText::_('COM_EASYSOCIAL_APPS_NO_APPS_FOUND');?>
						</td>
					</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="8" class="center">
						<div class="footer-pagination"><?php echo $pagination->getListFooter(); ?></div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<?php echo $this->html('form.hidden', 'customRedirect', 'index.php?option=com_easysocial&view=' . $view . '&layout=fields'); ?>
	<?php echo $this->html('form.action', $view, 'fields', $view); ?>

	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
</form>
