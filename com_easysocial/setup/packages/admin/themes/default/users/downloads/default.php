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
<form action="index.php" id="adminForm" method="post" name="adminForm" data-table-grid>
	<div class="panel-table">
		<table class="app-table table" data-pending-users>
			<thead>
				<tr>
					<th width="5">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					<th style="text-align: left;">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_NAME'); ?>
					</th>
					<th width="10%" class="center">
						<?php echo JText::_('COM_ES_TABLE_COLUMN_DOWNLOAD'); ?>
					</th>
					<th width="10%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATUS'); ?>
					</th>
					<th width="30%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'); ?>
					</th>
					<th width="1%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID');?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php if ($requests) { ?>
					<?php $i = 0; ?>
					<?php foreach ($requests as $request) { ?>
					<tr>
						<td>
							<?php echo $this->html('grid.id', $i++, $request->id); ?>
						</td>
						<td>
							<a href="index.php?option=com_easysocial&view=users&layout=form&id=<?php echo $request->getRequester()->id;?>"><?php echo $request->getRequester()->getName();?></a>
						</td>
						
						<td class="center">
							<?php if ($request->isReady()) { ?>
								<a href="index.php?option=com_easysocial&view=users&layout=downloadData&id=<?php echo $request->id;?>"><?php echo JText::_('COM_ES_DOWNLOAD');?></a>
							<?php } else { ?>
								&mdash;
							<?php } ?>
						</td>

						<td class="center">
							<?php echo $request->getStateLabel();?>
						</td>
						<td class="center">
							<?php echo ES::date($request->created)->format();?>
						</td>
						<td class="center">
							<?php echo $request->id;?>
						</td>
					</tr>
					<?php } ?>
				<?php } else { ?>
				<tr class="is-empty">
					<td colspan="8" class="center empty">
						<div>
							<?php echo JText::_('COM_EASYSOCIAL_USERS_NO_PENDING_USERS'); ?>
						</div>
					</td>
				</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="8">
						<div class="footer-pagination">
							<?php echo $pagination->getListFooter(); ?>
						</div>
					</td>
				</tr>
			</tfoot>

		</table>
	</div>

	<?php echo $this->html('form.action', 'users', 'download'); ?>
</form>
