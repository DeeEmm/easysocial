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

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.published', 'published', $published); ?>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--empty"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit' , $limit); ?>
			</div>
		</div>
	</div>

	<div class="panel-table">
		<table class="app-table table">
			<thead>
				<th width="1%" class="center">
					<?php echo $this->html('grid.checkAll'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'); ?>
				</th>
				<th class="center" width="5%">
					<?php echo $this->html('grid.sort', 'published', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATUS'), $ordering, $direction); ?>
				</th>
				<th width="10%" class="center">
					<?php echo $this->html('grid.sort', 'email', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_EMAIL_NOTIFICATIONS_DEFAULT'), $ordering, $direction); ?>
				</th>
				<th width="10%" class="center">
					<?php echo $this->html('grid.sort', 'system', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_SYSTEM_NOTIFICATIONS_DEFAULT'), $ordering, $direction); ?>
				</th>
				<th width="15%" class="center">
					<?php echo $this->html('grid.sort', 'email_published', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_EMAIL_NOTIFICATIONS_STATE'), $ordering, $direction); ?>
				</th>
				<th width="15%" class="center">
					<?php echo $this->html('grid.sort', 'system_published', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_SYSTEM_NOTIFICATIONS_STATE'), $ordering, $direction); ?>
				</th>
				<th width="10%" class="center">
					<?php echo $this->html('grid.sort', 'element', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ELEMENT'), $ordering, $direction); ?>
				</th>
				<th width="5%" class="center">
					<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering, $direction); ?>
				</th>
			</thead>

			<tbody>
			<?php if($alerts){ ?>
				<?php $i = 0; ?>
				<?php foreach($alerts as $alert){ ?>
				<tr>
					<td>
						<?php echo $this->html('grid.id', $i, $alert->id); ?>
					</td>
					<td>
						<?php echo $alert->getTitle();?>
					</td>
					<td class="center">
						<?php echo $this->html('grid.published', $alert, '', 'published'); ?>
					</td>
					<td class="center">
						<?php if($alert->email >= 0) { ?>
							<?php echo $this->html('grid.published', $alert, 'alerts', 'email', 'toggleEmailPublish'); ?>
						<?php } else { ?>
							<?php echo JText::_('COM_EASYSOCIAL_PROFILE_NOTIFICATION_NOT_APPLICABLE'); ?>
						<?php } ?>
					</td>
					<td class="center">
						<?php if($alert->system >= 0) { ?>
							<?php echo $this->html('grid.published', $alert, 'alerts', 'system', 'toggleSystemPublish'); ?>
						<?php } else { ?>
							<?php echo JText::_('COM_EASYSOCIAL_PROFILE_NOTIFICATION_NOT_APPLICABLE'); ?>
						<?php } ?>
					</td>
					<td class="center">
						<?php echo $this->html('grid.published', $alert, 'alerts', 'email_published', 'toggleAllowModifyEmail'); ?>
					</td>
					<td class="center">
						<?php echo $this->html('grid.published', $alert, 'alerts', 'system_published', 'toggleAllowModifySystem'); ?>
					</td>
					<td class="center">
						<?php echo ucfirst($alert->element);?>
					</td>
					<td class="center">
						<?php echo $alert->id;?>
					</td>
				</tr>
				<?php } ?>
			<?php } else { ?>
				<tr class="is-empty">
					<td colspan="9" class="empty center">
						<div>
							<?php echo JText::_('COM_EASYSOCIAL_ALERTS_LIST_EMPTY'); ?>
						</div>
					</td>
				</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="9">
						<div class="footer-pagination"><?php echo $pagination->getListFooter(); ?></div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
	<?php echo $this->html('form.action', 'alerts'); ?>
</form>

