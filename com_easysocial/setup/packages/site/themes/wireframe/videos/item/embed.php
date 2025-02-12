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
<!DOCTYPE html>
<head>
<base href="<?php echo JURI::root(); ?>" />
<?php foreach ($cssFiles as $cssFile) { ?>
<link href="<?php echo $cssFile;?>" rel="stylesheet" />
<?php } ?>

<?php echo ES::scripts()->getJavascriptConfiguration();?>

<?php foreach ($jsFiles as $jsFile) { ?>
<script src="<?php echo $jsFile;?>"></script>
<?php } ?>
</head>

<body style="margin: 0; padding: 0; overflow: hidden;">
<?php if ($video->isUpload()) { ?>
<script>
EasySocial.require()
.script('site/vendors/videojs', 'site/vendors/videojs/brand', 'site/vendors/videojs/watermark')
.done(function($) {

	var player = videojs('video-<?php echo $video->id;?>', {
		"controls": true,
		"poster": "<?php echo $video->getThumbnail();?>"
	}, function() {

	});

	<?php if ($this->config->get('video.layout.player.logo')) { ?>
	player.brand({
		image: "<?php echo $video->getPlayerLogo();?>",
		title: "<?php echo $this->jConfig->getValue('sitename');?>",
		destination: "<?php echo JURI::root();?>",
		destinationTarget: "_top"
	});
	<?php } ?>

	<?php if ($this->config->get('video.layout.player.watermark')) { ?>
	player.watermark({
		image: "<?php echo $video->getPlayerWatermark();?>",
		position: "<?php echo $this->config->get('video.layout.player.watermarkposition');?>",
		url: "<?php echo JURI::root();?>"
	});
	<?php } ?>
});
</script>
<?php } ?>
<div id="es">
	<?php if ($video->isUpload()) { ?>
	<div class="es-video-player">
		<div class="es-viewport">
			<video id="video-<?php echo $video->id;?>" class="video-js vjs-default-skin vjs-big-play-centered" width="100%" height="100%" preload="none">
				<source type="video/mp4" src="<?php echo $video->getFile();?>" />
			</video>
		</div>
	</div>
	<?php } else { ?>
	<div style="position: relative;
	width: 100%;
	height: 0;
	padding-bottom: 56.25%;">
		<?php

		$params = json_decode($video->params);

		$html = $params->oembed->html;
		$html = str_ireplace('width="' . $params->oembed->width . '"', 'style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"', $html);
		$html = str_ireplace('height="' . $params->oembed->height . '"', '', $html);
		?>
		<?php echo $html;?>
	</div>
	<?php } ?>
</div>
</body>
</html>
