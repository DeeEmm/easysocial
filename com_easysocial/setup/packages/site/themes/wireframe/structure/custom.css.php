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

$standardBg = $this->config->get('button.standard.bg', '#ffffff');
$standardTxt = $this->config->get('button.standard.text', '#333333');
$primaryBg = $this->config->get('button.primary.bg', '#4A90E2');
$primaryTxt = $this->config->get('button.primary.text', '#ffffff');
$dangerBg = $this->config->get('button.danger.bg', '#F65B5B');
$dangerTxt = $this->config->get('button.danger.text', '#ffffff');
$successBg = $this->config->get('button.success.bg', '#4FC251');
$successTxt = $this->config->get('button.success.text', '#ffffff');

// Get current theme
$theme = ES::themes();
$currentTheme = $theme->getCurrentTheme();
?>
<style type="text/css">
/* Max width specific stream elements due to it will affect vertical spaces. */
/*
#es .es-stream-preview .es-photos,
#es .es-stream-embed.is-link,
#es .es-stream-embed.is-ads,
#es .es-stream-embed.is-video {
	max-width: 640px;
}
*/
<?php if ($primaryBg != '#4A90E2') { ?>
#es .es-mobile-filter-slider .is-active .es-mobile-filter-slider__btn,
#es .es-mobile-filter-slider .is-active .es-mobile-filter-slider__btn:focus,
#es .es-mobile-filter-slider .is-active .es-mobile-filter-slider__btn:active,
#es .es-story-privacy .es-privacy .es-privacy-toggle,
#es .btn-es-primary {

	background: <?php echo $primaryBg;?> !important;
	color: <?php echo $primaryTxt;?> !important;
	border-color: rgba(<?php echo ES::string()->hexToRGB($primaryBg);?>, 0) !important;
}

#es .es-mobile-filter-slider .is-active .es-mobile-filter-slider__btn:hover,

#es .es-story-privacy .es-privacy .es-privacy-toggle:hover,
#es .es-story-privacy .es-privacy .es-privacy-toggle:focus,
#es .es-story-privacy .es-privacy .es-privacy-toggle:active,
#es .btn-es-primary:hover,
#es .btn-es-primary:active,
#es .btn-es-primary:focus {
	background-color: #<?php echo ES::colors($primaryBg)->darken(12); ?> !important;
}


<?php } ?>

<?php if ($dangerBg != '#F65B5B') { ?>
/** Danger **/
#es .btn-es-danger {
	background: <?php echo $dangerBg;?> !important;
	color: <?php echo $dangerTxt;?> !important;
	border-color: rgba(<?php echo ES::string()->hexToRGB($dangerBg);?>, 0) !important;
}

#es .btn-es-danger:hover,
#es .btn-es-danger:active,
#es .btn-es-danger:focus {
	background-color: #<?php echo ES::colors($dangerBg)->darken(12); ?> !important;
}

#es .btn-es-danger-o:hover, #es .btn-es-danger-o:active, #es .btn-es-danger-o {
	color: <?php echo $dangerBg;?> !important;
}
<?php } ?>

<?php if ($standardBg != '#333333') { ?>
/** Default **/
#es .es-mobile-filter-slider__btn,
#es .btn-es-default {
	background: <?php echo $standardBg;?> !important;
	color: <?php echo $standardTxt;?> !important;
	border: 1px solid rgba(<?php echo ES::string()->hexToRGB($standardTxt);?>, 0.2) !important;
}

#es .btn-es-default-o,
#es .btn-es-default-o:hover,
#es .btn-es-default-o:active,
#es .btn-es-default-o:focus {
	background: <?php echo $standardBg;?> !important;
	color: <?php echo $standardTxt;?> !important;
}
<?php } ?>

<?php if ($successBg != '#4FC251') { ?>
/** Success **/
#es .btn-es-success {
	background: <?php echo $successBg;?> !important;
	color: <?php echo $successTxt;?> !important;
	border-color: rgba(<?php echo ES::string()->hexToRGB($successBg);?>, 0) !important;
}

#es .btn-es-success:hover,
#es .btn-es-success:active,
#es .btn-es-success:focus {
	background-color: #<?php echo ES::colors($successBg)->darken(12); ?> !important;
}

#es .btn-es-success-o,
#es .btn-es-success-o:hover,
#es .btn-es-success-o:active,
#es .btn-es-success-o:focus {
	color: <?php echo $successBg;?> !important;
}
<?php } ?>

<?php
$model = ES::model('Background');
$presets = $model->getPresetBackgrounds();
?>
<?php foreach ($presets as $preset) { ?>

#es .es-stream .es-stream-content.es-story--bg-<?php echo $preset->id;?> a,
#es .es-stream .es-stream-content.es-story--bg-<?php echo $preset->id;?> a:hover,
#es .es-stream .es-stream-content.es-story--bg-<?php echo $preset->id;?> a:focus {
	font-weight: 700;
	text-decoration: underline;
	color: <?php echo $preset->params->get('text_color');?> !important;
}

#es .es-stream .es-stream-content.es-story--bg-<?php echo $preset->id;?> {
	line-height: 1.5;
	min-height: 40px;
	text-align: center;
	font-size: 20px;
	font-weight: bold;
	border-radius: 4px;
}

#es .es-stream .es-stream-content.es-story--bg-<?php echo $preset->id;?> .es-stream-info-meta {
	font-size: 16px;
	font-weight: normal;
}

#es .es-story--bg-<?php echo $preset->id;?> .es-story-panel-contents .es-story-panel-content {
	color: <?php echo $preset->params->get('text_color');?> !important;
}

#es .es-story--bg-<?php echo $preset->id;?> .es-story-textfield {
	font-size: 20px;
	line-height: 1.5;
	font-weight: 700;
}

#es .es-story--bg-<?php echo $preset->id;?> .es-story-textfield,
#es .es-story--bg-<?php echo $preset->id;?> {
	color: <?php echo $preset->params->get('text_color');?> !important;

	<?php if ($preset->params->get('type', 'gradient') == 'solid') { ?>
	background: <?php echo $preset->params->get('first_color'); ?> !important;
	<?php } ?>

	<?php if ($preset->params->get('type', 'gradient') == 'gradient') { ?>
	background-image: -webkit-linear-gradient(left, <?php echo $preset->params->get('first_color'); ?> 0%, <?php echo $preset->params->get('second_color'); ?> 100%) !important;
	background-image: -o-linear-gradient(left, <?php echo $preset->params->get('first_color'); ?> 0%, <?php echo $preset->params->get('second_color'); ?> 100%) !important;
	background-image: linear-gradient(to right, <?php echo $preset->params->get('first_color'); ?> 0%, <?php echo $preset->params->get('second_color'); ?> 100%) !important;
	background-repeat: repeat-x !important;
	<?php } ?>
}
#es .es-story--bg-<?php echo $preset->id;?> .es-story-meta,
#es .es-story--bg-<?php echo $preset->id;?> .es-stream-info-meta {
	color: <?php echo $preset->params->get('text_color');?> !important;
}
#es .es-story--bg-<?php echo $preset->id;?> .es-story-textfield::-webkit-input-placeholder {
	color: <?php echo $preset->params->get('text_color');?> !important;
	opacity: .7 !important;
}
#es .es-story--bg-<?php echo $preset->id;?> .es-story-textfield::-moz-placeholder {
	color: <?php echo $preset->params->get('text_color');?> !important;
	opacity: .7 !important;
}
#es .es-story--bg-<?php echo $preset->id;?> .es-story-textfield:-ms-input-placeholder {
	color: <?php echo $preset->params->get('text_color');?> !important;
	opacity: .7 !important;
}
#es .es-story--bg-<?php echo $preset->id;?> .es-story-textfield:-moz-placeholder {
	color: <?php echo $preset->params->get('text_color');?> !important;
	opacity: .7 !important;
}
<?php } ?>


<?php
// If there is custom reactions, we will use it
$override = ES::getCustomReactions();

if ($override) {
?>
#es .es-icon-reaction::before {background-image:url("<?php echo $override; ?>")}
<?php } ?>
</style>
