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

	<div class="panel-table">
		<table class="app-table table" data-stream-list>
			<thead>
				<tr>
					<th width="1%">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					<th>
						<?php echo JText::_('Icon'); ?>
					</th>
					<th width="35%" class="center">
						<?php echo JText::_('Published'); ?>
					</th>
					<th width="5%" class="center">
						<?php echo JText::_('ID');?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($reactions) { ?>
					<?php $i = 0; ?>

					<?php foreach ($reactions as $reaction) { ?>
					<tr data-id="<?php echo $reaction->table->id;?>">
						<td class="center">
							<?php echo $this->html('grid.id', $i, $reaction->table->id); ?>
						</td>
						<td>
							<div class="es-icon-reaction es-icon-reaction--sm es-icon-reaction--<?php echo $reaction->table->action;?>"></div>
						</td>
						<td class="center">
							<?php echo $this->html('grid.published', $reaction->table, 'reactions', 'published', ['publish', 'unpublish']); ?>
						</td>
						<td class="center">
							<?php echo $reaction->table->id; ?>
						</td>
					</tr>
					<?php $i++; ?>
					<?php } ?>
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

	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />

	<?php echo $this->html('form.action', 'reactions'); ?>
</form>
