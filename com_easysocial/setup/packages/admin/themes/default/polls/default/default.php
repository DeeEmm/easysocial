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
<form name="adminForm" id="adminForm" method="post" data-table-grid>

	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search', $search); ?>
		</div>
		<div class="app-filter-bar__cell app-filter-bar__cell--empty"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit' , $limit); ?>
			</div>
		</div>
	</div>

	<div id="pollsTable" class="panel-table" data-polls>
		<table class="app-table table">
			<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo $this->html('grid.checkAll'); ?>
					</th>

					<th>
						<?php echo $this->html('grid.sort', 'title', 'COM_EASYSOCIAL_TABLE_COLUMN_TITLE', $ordering, $direction); ?>
					</th>

					<th width="18%" class="center">
						<?php echo $this->html('grid.sort', 'created_by', 'COM_EASYSOCIAL_POLLS_CREATOR', $ordering , $direction ); ?>
					</th>

					<th width="10%" class="center">
						<?php echo $this->html('grid.sort', 'created', 'COM_EASYSOCIAL_POLLS_CREATED', $ordering , $direction ); ?>
					</th>

					<th width="5%" class="center">
						<?php echo $this->html('grid.sort', 'id', 'COM_EASYSOCIAL_POLLS_ID', $ordering , $direction ); ?>
					</th>
				</tr>
			</thead>

			<tbody>
			<?php if ($polls) { ?>
				<?php $i = 0; ?>
				<?php foreach ($polls as $poll) { ?>
				<tr data-poll-item data-title="<?php echo $this->html( 'string.escape' , $poll->title );?>" data-id="<?php echo $poll->id;?>">
					<td>
						<?php echo $this->html( 'grid.id' , $i , $polls[ $i ]->id ); ?>
					</td>

					<td style="text-align:left;">
						<?php echo ES::string()->escape($poll->title);?>
					</td>

					<td class="center">
						<span><?php echo $poll->creator->getName();?></span>
					</td>

					<td class="center">
						<span><?php echo $poll->created;?></span>
					</td>

					<td class="center">
						<?php echo $poll->id;?>
					</td>
				</tr>
				<?php $i++; ?>
				<?php } ?>

			<?php } else { ?>
			<tr>
				<td class="center" colspan="12">
					<div><?php echo JText::_( 'COM_EASYSOCIAL_POLLS_NO_POLLS_FOUND_BASED_ON_SEARCH_RESULT' ); ?></div>
				</td>
			</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="12">
						<div class="footer-pagination">
							<?php echo $pagination->getListFooter(); ?>
						</div>
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
	<input type="hidden" name="view" value="polls" />
	<input type="hidden" name="controller" value="polls" />
</form>
