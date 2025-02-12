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
<?php if (isset($isFeatured) && $isFeatured && isset($featuredVideos) && $featuredVideos) { ?>
	<?php echo $this->output('site/videos/default/items.header'); ?>
<?php } ?>

<div class="<?php echo !$videos ? ' is-empty' : '';?>">
	<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_VIDEOS_NO_VIDEOS_AVAILABLE_CURRENTLY', 'fa-film'); ?>

	<div class="es-cards es-cards--2">
		<?php if ($videos) { ?>
			<?php foreach ($videos as $video) { ?>
				<?php echo $this->loadTemplate('site/videos/default/item', array('video' => $video, 'uid' => $uid, 'utype' => $type, 'returnUrl' => $returnUrl, 'browseView' => $browseView, 'from' => $from)); ?>
			<?php } ?>
		<?php } ?>
	</div>

</div>

<?php if ($videos && isset($pagination) && $pagination) { ?>
	<?php echo $pagination->getListFooter('site');?>
<?php } ?>
