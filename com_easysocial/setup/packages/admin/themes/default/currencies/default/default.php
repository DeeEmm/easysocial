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
		<table class="app-table table">
			<thead>
				<?php if ($this->tmpl != 'component') { ?>
				<th width="1%" class="center">
					<?php echo $this->html('grid.checkAll'); ?>
				</th>
				<?php } ?>

				<th>
					<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'); ?>
				</th>

				<th width="30%" class="center">
					<?php echo JText::_('COM_ES_TABLE_COLUMN_SYMBOL'); ?>
				</th>

				<th width="<?php echo $this->tmpl == 'component' ? '8%' : '5%';?>" class="center">
					<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'); ?>
				</th>
			</thead>

			<tbody>
			<?php if ($currencies) { ?>
				<?php $i = 0; ?>
				<?php foreach ($currencies as $currency) { ?>
				<tr>
					<?php if($this->tmpl != 'component'){ ?>
					<td class="center">
						<?php echo $this->html('grid.id', $i, $currency->id); ?>
					</td>
					<?php } ?>

					<td>
						<a href="index.php?option=com_easysocial&view=currencies&layout=form&id=<?php echo $currency->id;?>">
							<?php echo JText::_($currency->title);?>
						</a>
					</td>

					<td class="center">
						<?php echo JText::_($currency->symbol);?>
					</td>

					<td class="center">
						<?php echo $currency->id;?>
					</td>
				</tr>
				<?php } ?>
			<?php } else { ?>
				<tr class="is-empty">
					<td class="empty" colspan="8">
						<?php echo JText::_('COM_ES_EMOTICONS_LIST_EMPTY'); ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>

	<?php echo $this->html('form.action', 'currencies', '', 'currencies'); ?>
</form>
