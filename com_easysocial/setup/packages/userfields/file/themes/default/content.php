<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div data-field-file data-maxfile="<?php echo $limit; ?>"
	data-message-working="<?php echo JText::_('COM_EASYSOCIAL_WORKING', true);?>"
	data-error-unknown="<?php echo JText::_('PLG_FIELDS_FILE_ERROR_UNKNOWN_ERROR_OCCURED', true);?>"
>
	<div data-field-file-list>
		<?php if (!empty($count)) { ?>
			<?php for($i = 0; $i < $count; $i++) { ?>
			<div class="data-field-file-item" data-field-file-item data-key="<?php echo $i; ?>">
				<?php echo $this->loadTemplate('fields/user/file/control', array('key' => $i, 'inputName' => $inputName, 'file' => $value[$i], 'field' => $field, 'params' => $params, 'tmp' => $tmp)); ?>
			</div>
			<?php } ?>
		<?php } ?>

		<?php if ($count < $limit) { ?>
		<div class="data-field-file-item" data-field-file-item data-key="<?php echo $count; ?>">
			<?php echo $this->loadTemplate('fields/user/file/upload', array('key' => $count, 'inputName' => $inputName)); ?>
		</div>
		<?php } ?>
	</div>

	<?php if (empty($limit) || ($count < ($limit - 1))) { ?>
		<a href="javascript:void(0);" class="btn btn-es-default btn-small" data-field-file-add><?php echo JText::_('PLG_FIELDS_FILE_ADD_ANOTHER_FILE'); ?></a>
	<?php } ?>

	<div class="t-fs--sm t-lg-mb--no" data-field-file-size>
		<?php echo JText::_('PLG_FIELDS_FILE_MAXIMUM_FILE_SIZE'); ?> <?php echo $params->get('size_limit'); ?> <strong><?php echo JText::_('MB'); ?></strong>
	</div>
</div>
