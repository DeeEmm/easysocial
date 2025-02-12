<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-story-files-content" data-files-canvas>
	<div class="es-story-files-dropsite" data-files-dropsite>
		<div class="es-story-files-upload" data-files-upload>
			<span>
				<b class="add-hint"><i class="fa fa-upload"></i><?php echo JText::_('APP_USER_FILES_STORY_ADD_FILES'); ?></b>
				<b class="drop-hint"><i class="fa fa-upload"></i><?php echo ES::getUploadMessage('files'); ?></b>
			</span>
		</div>
		<div class="es-story-files-items" data-files-items>
		<?php if (isset($data['files']) && $data['files']) { ?>
			<?php foreach ($data['files'] as $file) { ?>
			<?php echo $this->output('site/explorer/uploader/preview', array('file' => $file, 'isEdit' => $isEdit)); ?>
			<?php } ?>
		<?php } ?>
		</div>
	</div>

	<div data-files-progress>
		<div class="es-story-files-progress">
			<table>
				<tr class="upload-status">
					<td>
						<div class="upload-progress progress progress-striped active">
							<div class="upload-progress-bar bar progress-bar-info" style="width: 0%"><span class="upload-percentage"></span></div>
						</div>

						<div class="upload-remove-button">
							<i class="fa fa-times"></i>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
