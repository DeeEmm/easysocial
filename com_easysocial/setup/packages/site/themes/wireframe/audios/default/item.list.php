<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="<?php echo !$audios ? ' is-empty' : '';?>">
	<?php echo $this->html('html.emptyBlock', 'COM_ES_AUDIO_NO_AUDIO_AVAILABLE_CURRENTLY', 'fa-music'); ?>

	<div class="es-cards es-cards--2">
		<?php if ($audios) { ?>
			<?php foreach ($audios as $audio) { ?>
				<?php echo $this->loadTemplate('site/audios/default/item', array('audio' => $audio, 'uid' => $uid, 'utype' => $type, 'returnUrl' => $returnUrl, 'browseView' => $browseView, 'from' => $from, 'lists' => $lists)); ?>
			<?php } ?>
		<?php } ?>
	</div>
</div>

<?php if ($audios && isset($pagination) && $pagination) { ?>
	<?php echo $pagination->getListFooter('site');?>
<?php } ?>
