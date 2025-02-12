<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
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
			<table class="es-profile-data-table">
				<thead>
					<tr>
						<th colspan="2">
							<div class="o-grid-sm">
								<div class="o-grid-sm__cell">
									<?php echo $step->_('title');?>
								</div>

								<?php if (isset($canEdit) && $canEdit) { ?>
								<div class="o-grid-sm__cell o-grid-sm__cell--auto-size o-grid-sm__cell--right">
									<a href="<?php echo $step->getEditLink($item->getAlias() ? $item->getAlias() : null, isset($routerType) ? $routerType : null);?>" class="btn btn-xs btn-es-default-o">
										<i class="fa fa-pencil-alt"></i>
									</a>
								</div>
								<?php } ?>
							</div>
						</th>
					</tr>
				</thead>
				<?php $empty = true; ?>
				<?php $totalFields = count($step->fields); ?>
				<?php $i = 0; ?>

				<tbody>
					<?php foreach ($step->fields as $field) { ?>
						<?php if (!empty($field->output)) { ?>
							<?php echo $this->output('site/fields/about/field_output', array('field' => $field, 'item' => $item)); ?>
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
