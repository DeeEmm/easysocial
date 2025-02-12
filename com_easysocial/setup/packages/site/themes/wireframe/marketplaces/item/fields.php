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
<?php if ($steps) { ?>
	<?php foreach ($steps as $step) { ?>
		<?php if (!empty($step->fields)) { ?>
			<table class="table-es-market t-lg-mb--lg">
				<?php $empty = true; ?>
				<?php $totalFields = count($step->fields); ?>
				<?php $i = 0; ?>

				<thead>
					<tr>
						<th colspan="3">
							<?php echo $step->_('title');?>
						</th>
					</tr>
				</thead>

				<tbody>
					<?php foreach ($step->fields as $field) {  ?>
						<?php if (!empty($field->output) && $field->element !== 'header') { ?>
							<?php echo $this->output('site/marketplaces/item/field_output', ['field' => $field, 'item' => $item]); ?>
							<?php $empty = false; ?>
							<?php $i++; ?>

							<?php if ($i == $totalFields && !$item->isFieldVisible($field)) { ?>
							<tr>
								<td colspan="2"></td>
							</tr>
							<?php } ?>

						<?php } ?>
					<?php } ?>

					<?php if ($empty) { ?>
					<tr>
						<td colspan="2">
							<?php echo JText::_('COM_ES_ABOUT_NO_DETAILS_HERE');?>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		<?php } ?>
	<?php } ?>
<?php } ?>
