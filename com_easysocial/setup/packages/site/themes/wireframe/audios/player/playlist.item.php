<?php
/**
* @package      EasySocial
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
<div class="es-audio-playlist__item <?php echo $count == 1 ? 'is-active':''; ?>" 
	data-playlist-track 
	data-listmap-id="<?php echo $audio->listMapId; ?>"
	data-audio-id="<?php echo $audio->id; ?>"
	data-file="<?php echo $audio->getFile(); ?>"
	data-albumart="<?php echo $audio->getAlbumArt(); ?>">
	<div class="es-audio-playlist__no">
		<?php echo $count . '.'; ?>
	</div>
	<div class="es-audio-playlist__track">
		<span data-title><?php echo $audio->getTitle(); ?></span>
	</div>
	<div class="es-audio-playlist__album">
		<span><?php echo $audio->getAlbum(); ?></span>
	</div>
	<div class="es-audio-playlist__time" data-duration>
		<?php echo $audio->getDuration(); ?>
	</div>
	<div class="es-audio-playlist__action">
		<a href="javascript:void(0)" data-remove-track><i class="fa fa-trash"></i></a>
	</div>
</div>