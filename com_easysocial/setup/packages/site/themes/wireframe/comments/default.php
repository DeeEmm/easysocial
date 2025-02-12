<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-comments-wrapper" data-comments-wrapper>
	<div class="<?php echo !$comments && $hideEmpty ? ' t-hidden' : '';?>"
		data-es-comments
		data-group="<?php echo $group; ?>"
		data-element="<?php echo $element; ?>"
		data-verb="<?php echo $verb; ?>"
		data-uid="<?php echo $uid; ?>"
		data-count="<?php echo $count; ?>"
		data-total="<?php echo $total; ?>"
		data-grandtotal="<?php echo $totalComments; ?>"
		data-loadlimit="<?php echo ES::getLimit(); ?>"
		data-url="<?php echo empty($url) ? '' : $url; ?>"
		data-streamid="<?php echo empty($streamid) ? '' : $streamid; ?>"
		data-timestamp="<?php echo ES::date()->toUnix();?>"
		data-clusterid="<?php echo empty($clusterId) ? '' : $clusterId; ?>"
	>
		<?php if ($this->access->allowed('comments.read')) { ?>
		<div class="es-comments-control" data-comments-control>
			<div class="es-comments-control__load">
				<?php if ($total > $count) { ?>
				<div class="es-comments-load" data-comments-load>
					<a class="es-comments-control__link" data-comments-load-loadMore href="javascript:void(0);">
						<?php echo JText::_('COM_EASYSOCIAL_COMMENTS_ACTION_LOAD_MORE'); ?>
					</a>
				</div>
				<?php } ?>

				<div class="es-comments-control__stats" data-comments-stats><?php echo JText::sprintf('COM_ES_COMMENTS_TOTAL', '<i data-grandtotal>' . $totalComments . '</i>'); ?></div>
			</div>
		</div>
		<?php } ?>

		<?php if ($comments || ($this->access->allowed('comments.add') && $this->my->id)) { ?>
		<div class="es-comments-wrap">
			<ul class="es-comments" data-comments-list>
			<?php if ($this->access->allowed('comments.read') && $comments) { ?>
				<?php foreach ($comments as $comment) { ?>
					<?php echo $comment->renderHTML(['deleteable' => $deleteable, 'totalRepliesLimit' => $totalRepliesLimit]); ?>
				<?php } ?>
			<?php } ?>
			</ul>

			<?php if (!$hideForm && $this->access->allowed('comments.add') && $this->my->id) { ?>
				<?php echo $this->output('site/comments/form'); ?>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
</div>
