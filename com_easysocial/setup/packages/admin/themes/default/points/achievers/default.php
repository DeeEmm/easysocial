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
<form name="adminForm" id="adminForm" class="pointsForm" method="post" data-table-grid>
	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
		</div>
		
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__date-wrap">
				<div class="o-form-group">
					<strong><?php echo JText::_('Start Date');?>:</strong>
					<?php echo $this->html('form.calendar', 'start', $start, 'start', '', false, 'DD-MM-YYYY', false, false); ?>
				</div>

				<div class="o-form-group t-lg-ml--md">
					<strong><?php echo JText::_('End Date');?>:</strong>
					<?php echo $this->html('form.calendar', 'end', $end, 'end', '', false, 'DD-MM-YYYY', false, false); ?>
				</div>

				<div class="o-form-group">
					<button class="btn btn-es-default-o t-lg-ml--lg"><?php echo JText::_('Generate'); ?></button>
				</div>
			</div>
		</div>
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit' , $limit); ?>
			</div>
		</div>
	</div>

	<div id="pointsTable" class="panel-table">
		<table class="app-table table">
			<thead>
				<th>
					<?php echo JText::_('User'); ?>
				</th>

				<th width="25%" class="center">
					<?php echo JText::_('Points Collected During Period'); ?>
				</th>

				<th width="15%" class="center">
					<?php echo JText::_('Accummulated Points'); ?>
				</th>

				<th width="1%" class="center">
					<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'); ?>
				</th>
			</thead>
			
			<tbody>
				<?php if ($reports) { ?>
					<?php foreach ($reports as $report) { ?>
					<tr>
						<td>
							<a href="index.php?option=com_easysocial&view=users&layout=form&id=<?php echo $report->user->id;?>"><?php echo $report->user->getName(); ?></a>
						</td>
						
						<td class="center">
							<?php echo $report->total; ?>
						</td>

						<td class="center">
							<?php echo $report->user->getPoints();?>
						</td>

						<td class="center">
							<?php echo $report->user->id;?>
						</td>
					</tr>
					<?php } ?>
				<?php } else { ?>
					<tr>
						<td class="is-empty" colspan="4">
							<?php echo $this->html('html.emptyBlock', 'No achievers based on the current date range', 'fa-calendar'); ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
			

		</table>
	</div>

	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="controller" value="points" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_('form.token');?>
</form>
