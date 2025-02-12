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
<?php if ($video->isLargeEmbed()) { ?>
<script>
	EasySocial.require()
	.script('site/videos/preview')
	.done(function($) {
		$('[data-es-embed-container]').addController(EasySocial.Controller.Videos.Preview);
	});
</script>
<?php } ?>
<div class="es-stream-embed is-video">
	<div class="es-stream-embed__player">

		<?php if ($video->renderEmbedExternalVideo()) { ?>
			<?php echo $video->getEmbedCodes();?>
		<?php } ?>

		<?php if (!$video->renderEmbedExternalVideo()) { ?>
			<div class="video-container <?php echo $video->getFacebookEmbedRatioInMobile(); ?>"
				 data-video-container
				 data-video-ratio="<?php echo $video->isFacebookEmbed() ? $video->getRatioString() : ''; ?>">
				<?php if ($video->isLargeEmbed()) { ?>
				<div data-es-embed-container data-id="<?php echo $video->id; ?>">
					<div class="es-video-player" data-es-embed-preview>
						<div class="es-viewport">
							<div data-embed-player-wrapper tabindex="-1" preload="none" class="video-js vjs-default-skin vjs-big-play-centered vjs-paused vjs-controls-enabled vjs-workinghover vjs-v6 vjs-user-inactive" id="video-xxx" role="region" aria-label="Video Player" lang="en-gb">
								<div class="vjs-poster" tabindex="-1" aria-disabled="false" style="background-image: url(<?php echo $video->getThumbnail(); ?>);"></div>
								<button class="vjs-big-play-button" type="button" aria-live="polite" title="Play Video" aria-disabled="false" data-embed-play-button>
									<span aria-hidden="true" class="vjs-icon-placeholder"></span>
									<span class="vjs-control-text">Play Video</span>
								</button>
								<div class="vjs-loading-spinner" dir="ltr"></div>
							</div>
						</div>
					</div>

					<div data-es-embed-preview-result class="t-hidden"></div>
				</div>
			<?php } else { ?>
				<?php echo $video->getEmbedCodes();?>
			<?php } ?>
			</div>
		<?php } ?>
	</div>

	<a href="<?php echo $video->getPermalink();?>" class="es-stream-embed__title es-stream-embed--border"><?php echo $video->title;?></a>

	<div class="es-stream-embed__meta">
		<ul class="g-list-inline g-list-inline--space-right">
			<li>
				<a href="<?php echo $video->getCategory()->getPermalink(true, $uid, $utype);?>"><i class="fa fa-folder"></i>&nbsp; <?php echo JText::_($video->getCategory()->title);?></a>
			</li>
			<li>
				<i class="fa fa-calendar"></i>&nbsp; <?php echo $video->getCreatedDate()->format(JText::_('DATE_FORMAT_LC1'));?>
			</li>
			<?php if ($this->config->get('video.layout.item.hits')) { ?>
			<li>
				<i class="fa fa-eye"></i> <?php echo $video->getHits(); ?>
			</li>
			<?php } ?>
		</ul>
	</div>

	<div class="es-stream-embed__desc">
		<?php echo $this->html('string.truncate', $video->getDescription(), ES::config()->get('stream.content.truncatelength'), '', false, true);?>
	</div>
</div>
