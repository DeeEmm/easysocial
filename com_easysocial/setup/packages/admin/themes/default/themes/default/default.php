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

<div class="panel-table">
	<table class="app-table table">
		<thead>
			<th width="1%" class="center">
				&nbsp;
			</th>
			<th>
				<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'); ?>
			</th>
			<th width="5%" class="center">&nbsp;</th>
			<th width="1%" class="center">
				<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_DEFAULT'); ?>
			</th>
			<th width="10%" class="center">
				<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_VERSION'); ?>
			</th>
			<th width="10%" class="center">
				<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ELEMENT'); ?>
			</th>
			<th width="10%" class="center">
				<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'); ?>
			</th>
			<th width="20%" class="center">
				<?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_AUTHOR'); ?>
			</th>
		</thead>

		<tbody>
		<?php if( $themes ){ ?>

			<?php foreach ($themes as $theme) { ?>
			<tr>
				<td class="center">
					<input type="radio" name="cid[]" value="<?php echo $theme->element;?>" data-theme-item data-table-grid-id />
				</td>

				<td>
					<?php echo $theme->name; ?>
				</td>

				<td class="center">
					<a href="<?php echo JURI::base();?>index.php?option=com_easysocial&view=themes&layout=edit&element=<?php echo strtolower($theme->element);?>" class="btn btn-es-primary-o btn-sm"><?php echo JText::_('COM_ES_EDIT_FILES');?></a>
				</td>

				<td class="center">
					<?php echo $this->html('grid.featured', $theme, 'themes', 'default' , 'toggleDefault', !$theme->default ? true : false , array(JText::_('COM_EASYSOCIAL_THEMES_MAKE_DEFAULT'))); ?>
				</td>

				<td class="center">
					<?php echo $theme->version; ?>
				</td>

				<td class="center">
					<?php echo $theme->element; ?>
				</td>

				<td class="center">
					<?php echo $this->html( 'string.date' , $theme->created , 'd/m/Y' ); ?>
				</td>

				<td class="center">
					<a href="<?php echo $theme->website;?>" target="_blank"><?php echo $theme->author; ?></a>
				</td>
			</tr>
			<?php } ?>
		<?php } else { ?>
			<tr class="is-empty">
				<td colspan="6" class="empty center">
					<div><?php echo JText::_( 'COM_EASYSOCIAL_POINTS_LIST_EMPTY' ); ?></div>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>

<input type="hidden" name="boxchecked" value="" />
<input type="hidden" name="view" value="themes" />
<input type="hidden" name="controller" value="themes" />
<input type="hidden" name="task" value="" data-table-grid-task />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
