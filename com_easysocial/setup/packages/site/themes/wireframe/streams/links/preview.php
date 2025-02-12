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
<div class="es-stream-embed is-link">
	<?php if ($link->renderEmbedExternalVideoLink()) { ?>
		<?php echo $oembed->html; ?>
	<?php } else { ?>
		<?php if (!$link->isWordpressEmbed()) { ?>
			<?php if (isset($oembed->html) && !$oembed->isArticle) { ?>
				<div class="es-stream-embed__player">
					<div class="<?php echo isset($oembed->type) && $oembed->type == 'embed' ? 'embed-container' : 'video-container';?>
						<?php echo $link->isFacebookEmbed() && ((isset($oembed->type) && $oembed->type != 'embed') || !isset($oembed->type)) ? ' ' . $link->getRatioString() : ''; ?>
						<?php echo isset($oembed->wrapperClass) ? $oembed->wrapperClass : '';?>
					">
						<?php echo $oembed->html; ?>
					</div>
				</div>
			<?php } ?>

			<?php if ((!isset($oembed->html) || $oembed->isArticle) && $image) { ?>
				<a href="<?php echo $assets->get('link');?>" class="es-stream-embed__cover" target="_blank" <?php echo $params->get('stream_link_nofollow', false) ? ' rel="nofollow"' : '';?>>
					<div class="es-stream-embed__cover-img" style="background-image: url('<?php echo $image;?>');"></div>
				</a>
			<?php } ?>
		<?php } else { ?>
			<?php if ($image) { ?>
				<a href="<?php echo $assets->get('link');?>" class="es-stream-embed__cover" target="_blank" <?php echo $params->get('stream_link_nofollow', false) ? ' rel="nofollow"' : '';?>>
					<div class="es-stream-embed__cover-img" style="background-image: url('<?php echo $image;?>');"></div>
				</a>
			<?php } ?>
		<?php } ?>

		<a href="<?php echo $assets->get('link');?>" target="_blank" class="es-stream-embed__title <?php echo $image || isset($oembed->html) ? ' es-stream-embed--border' : '';?>" <?php echo $params->get('stream_link_nofollow', false) ? ' rel="nofollow"' : '';?>>
			 <?php echo $assets->get('title'); ?>
		</a>

		<div class="es-stream-embed__meta t-text--muted t-fs--sm">
			<?php echo $assets->get('link'); ?>
		</div>

		<div class="es-stream-embed__desc">
			<?php echo $content;?>
		</div>
	<?php } ?>
</div>

