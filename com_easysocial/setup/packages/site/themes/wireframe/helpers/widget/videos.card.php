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
<?php if ($videos) { ?>
	<?php foreach ($videos as $video) { ?>
	<div class="mod-card mod-card--no-avatar-holder">
		<a href="<?php echo $video->getPermalink();?>">
			<div class="mod-card__cover-wrap mod-card__cover-wrap--16-9">
				<div style="background-image : url(<?php echo $video->getThumbnail()?>);background-position: center center;" class="mod-card__cover"></div>

				<?php if ($showDuration) { ?>
				<div class="es-card__video-time"><?php echo $video->getDuration();?></div>
				<?php } ?>
			</div>
		</a>
		<div class="mod-card__context">
			<a class="es-card__title" href="<?php echo $video->getPermalink();?>"><?php echo $video->getTitle();?></a>

			<ul class="g-list-inline g-list-inline--dashed">
				<li>
					<a href="<?php echo $video->getCategory()->getPermalink(); ?>">
						<i class="fa fa-folder"></i> <?php echo JText::_($video->getCategory()->title); ?>
					</a>
				</li>

				<li>
					<a href="<?php echo $video->getAuthor()->getPermalink(); ?>">
						<i class="fa fa-user"></i> <?php echo $this->html('html.user', $video->getAuthor());?>
					</a>
				</li>
			</ul>
		</div>
		<div class="es-card__ft es-card--border">
			<ul class="g-list-inline g-list-inline--space-right">
				<li>
					<i class="fa fa-eye"></i> <?php echo $video->getHits();?>
				</li>
				<li>
					<i class="fa fa-heart"></i> <?php echo $video->getLikesCount();?>
				</li>
				<li>
					<i class="fa fa-comment"></i> <?php echo $video->getCommentsCount();?>
				</li>
			</ul>
		</div>
	</div>
	<?php } ?>
<?php } else { ?>
<div class="t-text--muted">
	<?php echo $emptyMessage; ?>
</div>
<?php } ?>
