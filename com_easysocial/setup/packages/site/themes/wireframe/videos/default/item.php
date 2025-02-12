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
<div class="es-cards__item" data-video-item data-id="<?php echo $video->id;?>">
	<div class="es-card <?php echo ($video->table->isFeatured()) ? 'is-featured' : ''; ?>">
		<div class="es-card__hd">
			<div class="es-card__action-group">
				<?php if ($video->canFeature() || $video->canUnfeature() || $video->canDelete() || $video->canEdit()) { ?>
				<div class="es-card__admin-action">
					<div class="pull-right dropdown_">
						<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm dropdown-toggle_" data-es-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
						<ul class="dropdown-menu">
							<?php if ($video->canFeature()) { ?>
							<li>
								<a href="javascript:void(0);" data-video-feature data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_FEATURE_VIDEO');?></a>
							</li>
							<?php } ?>

							<?php if ($video->canUnfeature()) { ?>
							<li>
								<a href="javascript:void(0);" data-video-unfeature data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_UNFEATURE_VIDEO');?></a>
							</li>
							<?php } ?>

							<?php if ($video->canEdit()) { ?>
							<li>
								<a href="<?php echo $video->getEditLink();?>"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_EDIT_VIDEO'); ?></a>
							</li>
							<?php } ?>

							<?php if ($video->canDelete()) { ?>
							<li class="divider"></li>

							<li>
								<a href="javascript:void(0);" data-video-delete data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_DELETE_VIDEO');?></a>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php } ?>
			</div>

			<?php if ($this->config->get('video.layout.item.duration')) { ?>
				<div class="es-card__video-time"><?php echo $video->getDuration();?></div>
			<?php } ?>

			<a href="<?php echo $video->getPermalink(true, $uid, $utype, $from);?>" class="embed-responsive embed-responsive-16by9">
				<div class="embed-responsive-item es-card__cover"
					style="
						background-image   : url('<?php echo $video->getThumbnail();?>');
						background-position: center center;"
				>
				</div>
			</a>
		</div>
		<div class="es-card__bd es-card--border">
			<div class="es-label-state es-label-state--featured es-card__state"><i class="es-label-state__icon"></i></div>
			<div class="es-card__title">
				<a href="<?php echo $video->getPermalink(true, $uid, $utype, $from);?>"><?php echo $video->getTitle();?></a>
			</div>

			<div class="es-card__meta t-lg-mb--sm">
				<ol class="g-list--horizontal">
					<li class="g-list__item">
						<a href="<?php echo $video->getCategory()->getPermalink(true, $uid, $utype);?>">
							<?php echo JText::_($video->getCategory()->title);?>
						</a>
					</li>

					<li class="t-lg-p--sm">&bull;</li>
					<li class="g-list__item"><?php echo $video->getCreatedDate()->format(JText::_('COM_EASYSOCIAL_DATE_DMY'));?></li>
				</ol>
			</div>

			<?php if ($browseView) { ?>
			<div class="es-card__meta t-lg-mb--sm">
				<ol class="g-list--horizontal">
					<li class="g-list__item"><?php echo $this->html('html.user', $video->creator, true);?></li>

				</ol>
			</div>
			<?php } ?>


		</div>
		<div class="es-card__ft es-card--border">
			<ul class="g-list-flex">

				<?php if ($this->config->get('video.layout.item.hits')) { ?>
				<li>
					<div><i class="fa fa-eye"></i> <?php echo $video->getHits();?></div>
				</li>
				<?php } ?>

				<li>
					<div><i class="fa fa-heart"></i> <?php echo $video->getLikesCount();?></div>
				</li>
				<li>
					<div><i class="fa fa-comment"></i> <?php echo $video->getCommentsCount();?></div>
				</li>
			</ul>
		</div>
	</div>
</div>
