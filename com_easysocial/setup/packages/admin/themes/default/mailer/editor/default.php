<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<form name="adminForm" id="adminForm" class="esForm" action="index.php" method="post" data-table-grid>

	<div class="panel-table">
        <table class="app-table table">
			<thead>
				<tr>
					<th width="1%">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					<th>
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_FILENAME'); ?>
					</th>
					<th width="35%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_FILE_DESCRIPTION'); ?>
					</th>
					<th width="40%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_LOCATION'); ?>
					</th>
					<th width="5%" class="center">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_MODIFIED'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($files) { ?>
					<?php $i = 0; ?>
					<?php foreach ($files as $file) { ?>
					<tr>
						<td class="center">
							<?php echo $this->html('grid.id', $i, base64_encode($file->relative)); ?>
						</td>
						<td>
							<a href="index.php?option=com_easysocial&view=mailer&layout=editfile&file=<?php echo urlencode($file->relative);?>"><?php echo $file->name; ?></a>
						</td>
						<td>
							<?php echo $file->desc;?>
						</td>
						<td>
							<?php echo str_ireplace(JPATH_ROOT, '', $file->path);?>
						</td>
						<td class="center">
							<?php echo $this->html('grid.published', $file, 'files', 'override', array(), array(0 => 'COM_ES_EMAIL_TEMPLATE_NOT_MODIFIED', 1 => 'COM_ES_EMAIL_TEMPLATE_MODIFIED'), array(), false); ?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>
				<?php } ?>
			</tbody>
		</table>
	</div>

	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="boxchecked" value="0" data-table-grid-box-checked />
	<input type="hidden" name="task" value="" data-table-grid-task />
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="view" value="mailer" />
	<input type="hidden" name="controller" value="mailer" />
</form>
