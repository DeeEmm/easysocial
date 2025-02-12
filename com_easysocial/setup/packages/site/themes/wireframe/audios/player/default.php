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
<div class="es-audio-player" data-audio-player data-id="<?php echo $audio->id; ?>" data-file="<?php echo $audio->getFile();?>">
	<div class="es-audio-player__overlay" data-overlay-notice style="display:none;">
		<div class="es-audio-player__overlay-content">
			<div>
				<i class="fa fa-check"></i> <?php echo JText::_('COM_ES_AUDIO_ADDED_TO_PLAYLIST'); ?>
			</div>
		</div>
	</div>
	<div class="es-audio-player__cover-wrap">
		<div class="es-audio-player__cover">
			<div class="es-audio-cover"
				style="background-image: url('<?php echo $audio->getAlbumArt(); ?>');"
			>
			</div>
		</div>
	</div>
	<div class="es-audio-player__content-wrap">
		<div class="es-audio-player__content" data-audio-container>
			<div class="es-audio-player__time" data-playing-duration><?php echo $audio->getDuration(); ?></div>
			<div class="es-audio-player__btn-play" data-play-button data-id="<?php echo $audio->id; ?>" data-file="<?php echo $audio->getFile();?>">
				<div class="o-loader o-loader--bottom"></div>
			</div>
			<div class="es-audio-player__btn-pause t-hidden" data-pause-button data-id="<?php echo $audio->id; ?>"></div>
			<div class="es-audio-player__wave">
				<div id="es-waveform-<?php echo $uid; ?>" class="es-wave-embed" data-audio-wave></div>
			</div>
			<div class="es-audio-player__background">
			</div>
		</div>
	</div>

</div>
