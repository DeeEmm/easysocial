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

$config = ES::config();

// Normalize options
$defaultOptions = [
	'size' => $config->get('photos.layout.size'),
	'mode' => $config->get('photos.layout.mode'),
	'ratio' => $config->get('photos.layout.ratio'),
	'threshold' => $config->get('photos.layout.threshold')
];

if (isset($options)) {
	$options = array_merge_recursive($options, $defaultOptions);
} else {
	$options = $defaultOptions;
}
?>

<div class="es-photos photos-<?php echo count($photos); ?> es-stream-photos pattern-tile"
	data-es-photo-group="<?php echo isset($album) && !empty($album) ? 'album:' . $album->id : ''; ?>"
	data-stream-item="<?php echo $this->input->get('view', '', 'string') == 'stream' && $this->input->get('layout', '', 'string') == 'item' ? '1' : '0'; ?>"
	<?php if (isset($streamItem) && isset($streamItem->verb)) { ?>
		data-es-photo-streamid="<?php echo $streamItem->uid; ?>"
	<?php } ?>
>
	<?php $i = 1; ?>
	<?php foreach ($photos as $photo) { ?>
	<div class="es-photo es-stream-photo ar-<?php echo $options['ratio']; ?>">
		<a href="<?php echo $photo->getPermalink();?>"
			data-es-photo="<?php echo $photo->id; ?>"
			title="<?php echo $this->html('string.escape', $photo->title . (($photo->caption!=='') ? ' - ' . $photo->caption : '')); ?>">
			<u><b data-mode="<?php echo $options['mode']; ?>"
				data-threshold="<?php echo $options['threshold']; ?>">
					<img src="<?php echo $photo->getSource($options['size']); ?>"
						 alt="<?php echo $this->html('string.escape', $photo->title . (($photo->caption!=='') ? ' - ' . $photo->caption : '')); ?>"
						 data-width="<?php echo $photo->getWidth(); ?>"
						 data-height="<?php echo $photo->getHeight(); ?>"
						 onload="window.ESImage ? ESImage(this) : (window.ESImageList || (window.ESImageList=[])).push(this);" />
			</b></u>
			<?php if (isset($remainingPhotoCount) && $remainingPhotoCount && $i == count($photos)) { ?>
			<div class="es-photo__note">
				+<?php echo $remainingPhotoCount; ?>
			</div>
			<?php } ?>
		</a>
	</div>
	<?php $i++; ?>
	<?php } ?>
</div>
