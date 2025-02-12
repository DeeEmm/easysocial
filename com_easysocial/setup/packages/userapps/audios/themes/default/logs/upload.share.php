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
<?php if (!$target) { ?>
	<?php echo JText::_('APP_USER_AUDIO_ACTIVITY_LOG_SHARED_AUDIO_OWN_TIMELINE');?>
<?php } else { ?>
	<?php echo $this->html('html.user', $actor->id);?>
	<i class="ies-arrow-right"></i>
	<?php echo $this->html('html.user', $target->id);?>
<?php } ?>

<div class="es-stream-audio-row mt-10 mb-10 es-stream-audios-1-4">
	<a href="<?php echo $audio->getPermalink();?>" class="es-stream-item-audio" data-es-audio="<?php echo $audio->id; ?>">
		<div style="background-image: url('<?php echo $audio->getAlbumArt();?>');" class="es-audio-image" data-audio-image="">
		</div>
	</a>
</div>
