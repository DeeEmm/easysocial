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
<form name="adminForm" id="adminForm" method="post" data-table-grid>
	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
		</div>

		<?php if ($this->tmpl != 'component') { ?>
		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.lists', array('field', 'others'), 'filter', $filter, JText::_('COM_EASYSOCIAL_FILTER_SELECT_TYPE') ); ?>
			</div>
		</div>
		<?php } ?>

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
				<th width="1%" class="center">
					<?php echo $this->html('grid.checkAll'); ?>
				</th>
				<th>
					<?php echo $this->html( 'grid.sort' , 'description' , JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_DESCRIPTION' ) , $ordering , $direction ); ?>
				</th>
				<th width="5%" class="center">
					<?php echo JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_STATUS' ); ?>
				</th>
				<th width="10%" class="center">
					<?php echo $this->html( 'grid.sort' , 'type' , JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_TYPE' ) , $ordering , $direction ); ?>
				</th>
				<th width="10%" class="center">
					<?php echo $this->html( 'grid.sort' , 'rule' , JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_RULE' ) , $ordering , $direction ); ?>
				</th>
				<th width="15%" class="center">
					<?php echo $this->html( 'grid.sort' , 'value' , JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_DEFAULT' ) , $ordering , $direction ); ?>
				</th>
				<th width="5%" class="center">
					<?php echo $this->html( 'grid.sort' , 'id' , JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_ID' ) , $ordering , $direction ); ?>
				</th>
			</thead>

			<tbody>
			<?php if( $privacy ){ ?>
				<?php $i = 0;?>
				<?php foreach( $privacy as $item ){ ?>
				<tr>
					<td>
						<?php echo $this->html( 'grid.id' , $i , $item->id ); ?>
					</td>
					<td>
						<a href="<?php echo FRoute::_( 'index.php?option=com_easysocial&view=privacy&layout=form&id=' . $item->id );?>">
							<?php echo JText::_( $item->description ); ?>
						</a>
					</td>
					<td class="center">
						<?php echo $this->html( 'grid.published' , $item , 'privacy' , 'state' ); ?>
					</td>
					<td class="center">
						<?php echo $item->type ?>
					</td>
					<td class="center">
						<?php echo $item->rule ?>
					</td>
					<td class="center">
						<?php
							$text = $lib->toKey( $item->value );
							echo JText::_( 'COM_EASYSOCIAL_PRIVACY_OPTION_'  . strtoupper( $text ) );
						?>
					</td>

					<td class="center">
						<?php echo $item->id;?>
					</td>
				</tr>
					<?php $i++; ?>
				<?php } ?>
			<?php } else { ?>
				<tr class="is-empty">
					<td colspan="6" class="empty center">
						<div><?php echo JText::_( 'COM_EASYSOCIAL_PRIVACY_LIST_EMPTY' ); ?></div>
					</td>
				</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="6">
						<?php if( $privacy ){ ?>
						<div class="footer-pagination"><?php echo $pagination->getListFooter();?></div>
						<?php } ?>
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
	<input type="hidden" name="view" value="privacy" />
	<input type="hidden" name="controller" value="privacy" />
</form>
