<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-video-player">
	<div class="es-viewport">
		<video id="video-<?php echo $uid;?>" class="video-js vjs-default-skin vjs-big-play-centered" width="100%" height="100%" preload="none" data-id="<?php echo $video->id; ?>">
			<source type="video/mp4" src="<?php echo $video->getFile();?>" />
		</video>
	</div>
</div>