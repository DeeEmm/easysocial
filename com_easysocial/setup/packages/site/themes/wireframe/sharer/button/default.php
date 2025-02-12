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
<html>
<head>
<?php foreach ($assets->stylesheets as $cssFile) { ?>
<link href="<?php echo $cssFile;?>" rel="stylesheet" />
<?php } ?>
</head>
<body onload="documentLoaded();">
<style type="text/css">
body {margin:0;padding:0;}
body #es .es-btn-sharer {
	background-color: <?php echo $this->config->get('sharer.buttoncolour');?>;
	text-decoration: none !important;
	-webkit-box-shadow: none;
	box-shadow: none;
	text-shadow: none;
	display: inline-block;
	margin-bottom: 0;
	font-weight: bold;
	text-align: center;
	vertical-align: middle;
	cursor: pointer;
	background-image: none;
	border: 1px solid transparent;
	white-space: nowrap;
	padding: 8px;
	font-size: 13px;
	line-height: 1.428571429;
	border-radius: 4px;
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	-o-user-select: none;
	user-select: none;
	color: <?php echo $this->config->get('sharer.textcolour');?>;
}
</style>

<div id="es">
	<?php if ($this->config->get('sharer.style') == 'full') { ?>
	<a href="javascript:void(0);" class="es-btn-sharer" onclick="shareButton();">
		<img src="<?php echo ES::sharer()->getLogo();?>" width="16" alt="">&nbsp; <?php echo $text;?>
	</a>
	<?php } ?>

	<?php if ($this->config->get('sharer.style') == 'icon') { ?>
	<a href="javascript:void(0);" class="es-btn-sharer" onclick="shareButton();">
		<img src="<?php echo ES::sharer()->getLogo();?>" width="16" alt="">
	</a>
	<?php } ?>

	<?php if ($this->config->get('sharer.style') == 'text') { ?>
	<a href="javascript:void(0);" class="es-btn-sharer" onclick="shareButton();"><?php echo $text;?></a>
	<?php } ?>
</div>

<script>
window.shareButton = function() {
	// Width for the popup
	var width = 480;
	var height = 640;

	// Get the proper top and left for dual screen monitors
	var windowWidth = window.screen.availLeft == 0 ? screen.width : window.screen.availLeft;
	var windowHeight = window.screen.availTop == 0 ? screen.height : window.screen.availTop;

	var top = (windowHeight / 2) - (height / 2);
	var left = (windowWidth / 2) - (width / 2);

	var url = '<?php echo ESR::sharer(array('url' => urlencode($link), 'aff' => $affiliationId), false);?>';

	window.open(url, '', 'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top);
};

window.documentLoaded = function() {
	var button = document.querySelector('.es-btn-sharer');
	var width = button.offsetWidth;
	var height = button.offsetHeight;
	var imageWidth = 0;
	var imageHeight = 0;

	<?php if ($this->config->get('sharer.style') == 'full' || $this->config->get('sharer.style') == 'icon') { ?>
		imageWidth = 16;
		imageHeight = 16;
	<?php } ?>

	width += imageWidth;
	height += imageHeight;

	parent.window.postMessage({
		"id": "<?php echo $id;?>",
		"width": width,
		"height": height
	}, atob('<?php echo $source;?>'));
};
</script>

</body>
</html>
