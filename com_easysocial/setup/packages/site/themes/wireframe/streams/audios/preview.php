<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-stream-embed is-audio is-<?php echo strtolower($audio->getLinkProvider()); ?>">
	<div class="es-stream-embed__player">
		<?php if ($audio->isUpload()) { ?>
			<?php echo $audio->getUploadEmbedCodes(); ?>
		<?php } else { ?>
			<div class="es-audio-container is-<?php echo strtolower($audio->getLinkProvider()); ?> <?php echo $audio->isSpotifyPodcast() ? 'is-podcast' : ''; ?>">
				<?php echo $audio->getLinkEmbedCodes(); ?>
			</div>
		<?php } ?>
	</div>
	<a href="<?php echo $audio->getPermalink();?>" class="es-stream-embed__title es-stream-embed--border">
		 <?php echo $audio->title;?>
	</a>
	<div class="es-stream-embed__meta">
		<ul class="g-list-inline g-list-inline--space-right">
			<li>
				<a href="<?php echo $audio->getGenre()->getPermalink();?>"><i class="fa fa-music"></i>&nbsp; <?php echo JText::_($audio->getGenre()->title);?></a>
			</li>
			<li>
				<i class="fa fa-calendar"></i>&nbsp; <?php echo $audio->getCreatedDate()->format(JText::_('DATE_FORMAT_LC1'));?>
			</li>
			<?php if ($this->config->get('audio.layout.item.hits')) { ?>
				<li>
					<i class="fa fa-headphones"></i> <?php echo $audio->getHits();?>
				</li>
			<?php } ?>
		</ul>
	</div>
	<div class="es-stream-embed__desc">
		<?php echo $this->html('string.truncate', $audio->description, ES::config()->get('stream.content.truncatelength'), '', false, true);?>
	</div>
</div>

