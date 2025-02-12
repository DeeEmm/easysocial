<?php
/**
* @package	  EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div data-audio-playlist data-id="<?php echo $activeList->id; ?>">
<?php echo $this->output('site/audios/player/playlist.header'); ?>
	<div class="es-audio-playlist-player" data-audio-playlist-player>
		<div class="es-audio-playlist-player__cell-1">
			<div class="es-audio-playlist-player__cover">
				<div class="es-audio-cover es-audio-cover--sm" style="background-image: url('');" data-playing-albumart>
				</div>
			</div>
		</div>
		<div class="es-audio-playlist-player__cell-2">
			<div class="o-loader o-loader--bottom"></div>
			<div class="es-audio-playlist-player__info">
				<div class="es-audio-playlist-player__state" data-playing-info>
					<?php if ($audios) { ?>
						<?php echo JText::_('COM_ES_AUDIO_NOW_PLAYING'); ?>
					<?php } else { ?>
						<?php echo JText::_('COM_ES_AUDIO_PLAYLIST_EMPTY'); ?>

						<?php if ($activeList->isOwner()) { ?>
							<a href='javascript:void(0);' data-add><?php echo JText::_('COM_ES_AUDIO_PLAYLIST_ADD_AUDIO'); ?></a>
						<?php } ?>
					<?php } ?>
				</div>
				<div class="es-audio-playlist-player__title" data-playing-title>
				</div>
			</div>
			<div class="es-audio-playlist-player__time" data-playing-duration>
			</div>
			<div class="es-audio-playlist-player__wave">
				<div id="es-waveform-playlist">
				</div>
			</div>	
		</div>

		<div class="es-audio-playlist-player__cell-3">
			<div class="es-audio-playlist-player__action-bar">
				<div class="es-audio-player-actionbar">
					<div class="es-audio-player-actionbar__control-backward" data-prev-button>
						
					</div>
					<div class="es-audio-player-actionbar__control-play" data-playpause-button>
						
					</div>
					<div class="es-audio-player-actionbar__control-forward" data-next-button>
						
					</div>
					<div class="es-audio-player-actionbar__control-volume" data-volume-button>
						<div class="es-volume-panel">
							<div id="es-volume-slider" data-volume-slider>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		
	</div>

	<div class="es-audio-playlist" data-playlist>
		<?php $count = 0; ?>
		<?php foreach ($audios as $audio) { ?>
			<?php $count++; ?>
			<?php echo $this->loadTemplate('site/audios/player/playlist.item', array('audio' => $audio, 'count' => $count)); ?>
		<?php } ?>
	</div>
	
</div>