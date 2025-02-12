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
<?php echo JText::sprintf('APP_USER_AUDIO_ACTIVITY_LOG_UPLOADED_AUDIO_IN_GENRE', '<a href="' . $genre->getPermalink() . '">' . $genre->get('title') . '</a>'); ?>

<div class="es-stream-audio-row mt-10 mb-10">
	<a class="es-stream-item-audio" href="<?php echo $audio->getPermalink();?>">
		<div data-audio-image="" class="es-audio-image" style="background-image: url('<?php echo $audio->getAlbumArt();?>');"></div>
	</a>
</div>
