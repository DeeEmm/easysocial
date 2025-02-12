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
<div class="fd-explorer-file" data-id="<?php echo $file->id; ?>" data-preview-uri="<?php echo $file->data->previewUri; ?>">
	<div class="btn-file-action-group">
		<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm t-lg-mr--md" data-fd-explorer-preview-button><i class="fa fa-eye"></i></a>
		<?php if ($file->canDelete) { ?>
		<a href="javascript: void(0);" class="btn btn-danger btn-sm btn-file-remove" data-fd-explorer-delete-button>
			<i class="fa fa-times"></i>
		</a>
		<?php } ?>	
	</div>

	<div class="o-checkbox">
		<?php if ($file->canDelete) { ?>
		<input id="fd-explorer-file--<?php echo $file->id; ?>" type="checkbox" value="<?php echo $file->id; ?>" data-fd-explorer-select>
		<label for="fd-explorer-file--<?php echo $file->id; ?>">
		<?php } ?>
			<div class="file-title"><i class="<?php echo $file->data->icon; ?>"></i> <?php echo $file->name; ?></div>
			<div class="file-meta"><?php echo ES::date($file->data->created)->format(JText::_('DATE_FORMAT_LC1'), true); ?></div>
		<?php if ($file->canDelete) { ?>
		</label>
		<?php } ?>
	</div>
</div>
