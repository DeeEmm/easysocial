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
<div class="row">
	<div class="col-md-12">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_MAINTENANCE_EXECUTING_SCRIPTS'); ?>

			<div class="panel-body">
				<table class="table table-striped table-noborder" data-table-scripts>
					<thead>
						<tr>
							<th><?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'); ?></td>
							<th width="10%" class="center"><?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATUS'); ?></td>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($scripts as $script) { ?>
						<tr data-row data-key="<?php echo $script->key; ?>">
							<td><?php echo $script->title; ?></td>
							<td class="center"><span class="label label-warning" data-status><i data-icon class="fa fa-wrench-3"></i></span></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</th>
		</div>
	</div>
</div>
