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
				<?php if ($this->tmpl != 'component') { ?>
				<th width="1%" class="center">
					<?php echo $this->html('grid.checkAll'); ?>
				</th>
				<?php } ?>

				<th>
					<?php echo JText::_('COM_ES_TABLE_COLUMN_COMMENT'); ?>
				</th>

				<th width="10%" class="center">
					&nbsp;
				</th>

				<th width="10%" class="center">
					<?php echo JText::_('COM_ES_TABLE_COLUMN_ACTIVITY_STREAM'); ?>
				</th>

				<th width="20%" class="center">
					<?php echo JText::_('COM_ES_TABLE_COLUMN_USER'); ?>
				</th>

				<th width="15%" class="center">
					<?php echo $this->html('grid.sort', 'created', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_CREATED'), $ordering, $direction); ?>
				</th>

				<th width="<?php echo $this->tmpl == 'component' ? '8%' : '5%';?>" class="center">
					<?php echo $this->html('grid.sort', 'id', JText::_('COM_EASYSOCIAL_TABLE_COLUMN_ID'), $ordering, $direction); ?>
				</th>
			</thead>

			<tbody>
			<?php if ($comments) { ?>
				<?php $i = 0; ?>
				<?php foreach ($comments as $comment) { ?>
				<tr>
					<?php if ($this->tmpl != 'component') { ?>
					<td class="center">
						<?php echo $this->html('grid.id', $i, $comment->id); ?>
					</td>
					<?php } ?>

					<td>
						<?php $giphy = $comment->hasGiphy(); ?>
						<?php if ($giphy) { ?>
							<img src="<?php echo $giphy;?>" width="64" />
						<?php } ?>

						<div data-comment-wrapper>
							<?php echo $comment->getComment();?>
						</div>
					</td>

					<td class="center">
						<a target="_blank" href="<?php echo rtrim(JURI::root(), '/') . '/' . $comment->getPermalink(false);?>" class="btn btn-default">
							<i class="fdi far fa-eye"></i>&nbsp; <?php echo JText::_('COM_ES_VIEW_LINK'); ?>
						</a>
					</td>

					<td class="center">
						<?php if ($comment->stream_id) { ?>
							<a class="es-state-publish" href="javascript:void(0);" disabled="disabled"></a>
						<?php } else { ?>
							<a class="es-state-unpublish" href="javascript:void(0);" disabled="disabled"></a>
						<?php } ?>
					</td>

					<td class="center">
						<a href="index.php?option=com_easysocial&view=users&layout=form&id=<?php echo $comment->created_by;?>"><?php echo $comment->getAuthor()->getName();?></a>
					</td>

					<td class="center">
						<?php echo $comment->created;?>
					</td>

					<td class="center">
						<?php echo $comment->id;?>
					</td>
				</tr>
				<?php } ?>
			<?php } else { ?>
				<tr class="is-empty">
					<td class="empty" colspan="8">
						<?php echo JText::_( 'COM_EASYSOCIAL_BADGES_LIST_EMPTY' ); ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="8">
						<div class="footer-pagination"><?php echo $pagination->getListFooter(); ?></div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>

	<?php echo $this->html('form.action', 'comments'); ?>
	<input type="hidden" name="ordering" value="<?php echo $ordering;?>" data-table-grid-ordering />
	<input type="hidden" name="direction" value="<?php echo $direction;?>" data-table-grid-direction />
</form>
