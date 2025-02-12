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
<form action="index.php" method="post" name="adminForm" class="esForm" id="adminForm" data-reports data-table-grid>

	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
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
				<tr>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>
					<th>
						<?php echo $this->html( 'grid.sort' , 'id' , JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_REPORTED_OBJECT' ) , $ordering , $direction ); ?>
					</th>
					<th class="center" width="10%">
						<?php echo $this->html( 'grid.sort' , 'total' , JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_TOTAL_REPORTS' ) , $ordering , $direction ); ?>
					</th>
					<th class="center" width="10%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_REPORTS'); ?>
					</th>
					<th class="center" width="10%">
						<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TYPE'); ?>
					</th>
					<th class="center" width="10%">
						<?php echo $this->html( 'grid.sort' , 'extension' , JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_EXTENSION' ) , $ordering , $direction ); ?>
					</th>
					<th width="10%" class="center">
						<?php echo $this->html( 'grid.sort' , 'extension' , JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_CREATED' ) , $ordering , $direction ); ?>
					</th>
					<th width="5%" class="center">
						<?php echo $this->html( 'grid.sort' , 'id' , JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_ID' ) , $ordering , $direction ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if( $reports ){ ?>
					<?php $i = 0; ?>
					<?php foreach( $reports as $report ){ ?>
					<tr class="row<?php echo $i; ?>"
						data-reports-item
						data-id="<?php echo $report->id;?>"
						data-type="<?php echo $report->type;?>"
						data-uid="<?php echo $report->uid; ?>"
						data-extension="<?php echo $report->extension;?>"
					>
						<td align="center">
							<?php echo $this->html( 'grid.id' , $i , $report->id ); ?>
						</td>
						<td style="text-align:left;">
							<a href="<?php echo $report->url;?>" class="mr-5" target="_blank">
								<?php echo $report->_('title'); ?>
							</a>
						</td>
						<td class="center">
							<?php echo $report->total; ?>
						</td>
						<td class="center">
							<a href="javascript:void(0);" data-reports-item-view-reports>
								<?php echo JText::_('COM_EASYSOCIAL_REPORTS_VIEW_REPORTS');?>
							</a>
						</td>
						<td class="center">
							<?php echo ucfirst($report->get('type'));?>
						</td>
						<td class="center">
							<?php echo $report->_('extension'); ?>
						</td>
						<td class="center">
							<?php echo $report->created; ?>
						</td>
						<td class="center">
							<?php echo $report->id; ?>
						</td>
					</tr>
					<?php } ?>
				<?php } else { ?>
					<tr class="is-empty">
						<td colspan="10" class="center empty">
							<?php echo JText::_( 'COM_EASYSOCIAL_NO_REPORTS_AVAILABLE_CURRENTLY' );?>
						</td>
					</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="10" class="center">
						<div class="footer-pagination"><?php echo $pagination->getListFooter(); ?></div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>


<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
<input type="hidden" name="boxchecked" value="0" data-table-grid-box-checked />
<input type="hidden" name="task" value="" data-table-grid-task />
<input type="hidden" name="option" value="com_easysocial" />
<input type="hidden" name="view" value="reports" />
<input type="hidden" name="controller" value="reports" />
</form>
