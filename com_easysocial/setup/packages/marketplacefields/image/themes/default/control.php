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

// dump($this->html('string.escape', $file->name));
?>
<div data-field-file-item-wrap class="file-wrap">
	<div class="file-name">
		<a href="javascript:void(0);" class="file-move" data-field-file-move><i class="fa fa-bars"></i></a>
		<button class="close" type="button" data-field-file-delete>×</button>
		<?php if ($params->get('allow_download') && empty($tmp)) { ?><a href="<?php echo $file->downloadLink; ?>" target="_blank"><?php } ?>
		<?php if (empty($tmp) && $params->get('allow_preview') && $file->hasPreview()) { ?>
		<div class="text-center mt-5 mb-5"><img src="<?php echo $file->previewLink; ?>" style="max-height: <?php echo $params->get('preview_max_height'); ?>px; max-width: <?php echo $params->get('preview_max_width'); ?>px;" /></div>
		<?php } ?>

		<?php if (isset($tmp) && $tmp) { ?>
			<span data-field-file-name><?php echo $file->name; ?></span>
		<?php } else { ?>
			<?php echo $this->html('string.escape', $file->name); ?>
		<?php } ?>

		<?php if ($params->get('allow_download') && empty($tmp)) { ?></a><?php } ?>
	</div>

	<input type="hidden" name="<?php echo $inputName; ?>[<?php echo $key; ?>][tmp]" value="<?php echo empty( $tmp ) ? 0 : 1; ?>" data-field-file-tmp />
	<input type="hidden" name="<?php echo $inputName; ?>[<?php echo $key; ?>][id]" value="<?php echo $file->id; ?>" data-field-file-id />
</div>
