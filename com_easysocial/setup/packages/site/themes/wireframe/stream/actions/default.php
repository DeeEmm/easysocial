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
<div class="es-actions es-bleed--bottom" data-stream-actions>
	<div class="es-actions__item es-actions__item-action">
		<?php if ($showLikes || $showComments || $showRepost || $showSharing) { ?>
		<div class="es-actions-wrapper">
			<ul class="es-actions-list">

				<?php if ($showLikes) { ?>
				<li class="action-title-likes streamAction" data-action>
					<span data-type="likes"><?php echo $likes->button(); ?></span>
				</li>
				<?php } ?>

				<?php if ($showComments) { ?>
				<li class="action-title-comments streamAction" data-action>
					<a href="javascript:void(0);" data-type="comments"><?php echo JText::_('COM_EASYSOCIAL_STREAM_COMMENT'); ?></a>
				</li>
				<?php } ?>

				<?php if ($showRepost) { ?>
				<li class="action-title-repost streamAction" data-action>
					<?php echo $repost->getButton(); ?>
				</li>
				<?php } ?>

				<?php if ($showSharing) { ?>
				<li class="action-title-social streamAction" data-action>
					<?php echo $sharing->html(); ?>
				</li>
				<?php } ?>

				<?php if (!$this->isMobile() && $this->my->id && $repost && $repost instanceof SocialRepost) { ?>
				<li class="action-title-repost-counter">
					<?php echo $repost->counter();?>
				</li>
				<?php } ?>
			</ul>
		</div>
		<?php } ?>

	</div>

	<?php if ($likes) { ?>
	<div class="es-actions__item es-actions__item-stats">
		<?php echo $likes->html(); ?>
	</div>
	<?php } ?>

	<div class="es-actions__item es-actions__item-comment">
		<?php if ($showCommentsListing) { ?>
			<?php echo $comments->html(array('hideEmpty' => true, 'hideForm' => !$showCommentsForm)); ?>
		<?php } ?>
	</div>
</div>
