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
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<?php foreach ($assets->stylesheets as $cssFile) { ?>
<link href="<?php echo $cssFile;?>" rel="stylesheet" />
<?php } ?>

<?php if (!$this->config->get('general.jquery')) { ?>
<script src="<?php echo JURI::root(true);?>/media/jui/js/jquery.min.js"></script>
<script src="<?php echo JURI::root(true);?>/media/jui/js/bootstrap.min.js"></script>
<?php } ?>

<?php echo ES::scripts()->getJavascriptConfiguration();?>

<?php foreach ($assets->scripts as $jsFile) { ?>
<script src="<?php echo $jsFile;?>"></script>
<?php } ?>

<style type="text/css">
body {
	margin: 0;
	padding: 0;
}
#es .es-sharer__title {
	border-bottom: 1px solid <?php echo $this->config->get('sharer.popupbordercolour');?>;
	background: <?php echo $this->config->get('sharer.popupcolour');?>;
}
#es .es-sharer__action {
	border-top: 1px solid <?php echo $this->config->get('sharer.popupbordercolour');?>;
	width: 100%;
	background: <?php echo $this->config->get('sharer.popupcolour');?>;
	position: fixed;
	bottom: 0;
	right: 0;
}

#es .es-sharer__stream {
	max-width: 100%;
}

#es .es-sharer__editor textarea {
	border: 0;
	min-height: 100px;
	resize: none;
	border: none;
	overflow: auto;
	outline: none;

	-webkit-box-shadow: none;
	-moz-box-shadow: none;
	box-shadow: none;
}

#es .es-stream-embed,
#es .es-stream-embed__cover {
	border-radius: 0;
}

#es .es-story-submit {
	border-radius: 3px !important;
}
</style>

</head>
<body>
<?php echo $contents;?>

<script>
window.closeWindow = function() {
	window.close();
};
</script>
</body>
</html>
